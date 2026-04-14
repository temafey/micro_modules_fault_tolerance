<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\RabbitEnqueue;

use MicroModule\Base\Domain\Exception\LoggerException;
use MicroModule\Base\Utils\LoggerTrait;
use MicroModule\FaultTolerance\CircuitBreaker\CircuitBreakerInterface;
use MicroModule\FaultTolerance\RabbitEnqueue\Exception\MessageWasNotSentToQueueException;
use Closure;
use DeepCopy\DeepCopy;
use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use Enqueue\Rpc\Promise;
use Throwable;

/**
 * Fault-tolerant wrapper for Enqueue Producer.
 *
 * Handles TCP connection loss to RabbitMQ by:
 * 1. Catching socket/connection exceptions from bunny/bunny
 * 2. Force-resetting the AMQP connection (channel + client + factory cache)
 * 3. Retrying with fixed backoff (configurable)
 * 4. Reporting success to circuit breaker to allow recovery
 *
 * The reset mechanism uses the SAME 3-layer approach as QueueFaultTolerantConsumer:
 * - Try graceful context.close() (swallow exception on dead TCP)
 * - Null AmqpContext.$bunnyChannel (forces factory to create fresh channel)
 * - Null AmqpConnectionFactory.$client (forces fresh BunnyClient + TCP)
 *
 * This is critical for long-running CLI workers (e.g., outbox publishers) that
 * hold a Producer reference across multiple publishes and need to survive
 * RabbitMQ restarts or transient network loss.
 */
class QueueFaultTolerantProducer implements ProducerInterface
{
    use PrivateTrait, LoggerTrait;

    public const ENQUEUE_PRODUCER_SERVICE_NAME = 'enqueue_producer';
    protected const DEFAULT_RETRY_TIMEOUT = 1000000;
    protected const ENQUEUE_CONTEXT_CHANNEL_PROPERTY_NAME = 'bunnyChannel';
    protected const ENQUEUE_CONTEXT_CHANNEL_FACTORY_PROPERTY_NAME = 'bunnyChannelFactory';

    /**
     * Queue producer.
     *
     * @var ProducerInterface
     */
    protected $originalQueueProducer;

    /**
     * Pristine clone for queue producer (used as reset template).
     *
     * @var ProducerInterface
     */
    protected $originalQueueProducerClone;

    /**
     * Circuit breaker counts each failure and once you reach limit it will skip connection attempt with instant failure.
     *
     * @var CircuitBreakerInterface
     */
    protected $circuitBreaker;

    /**
     * Special object for cloning.
     *
     * @var DeepCopy
     */
    protected $deepCopyCloner;

    /**
     * Connection timeout retry in microsecond.
     *
     * @var int
     */
    protected $retryTimeout;

    /**
     * ProgramResultRepository constructor.
     *
     * @param ProducerInterface       $originalQueueProducer
     * @param CircuitBreakerInterface $circuitBreaker
     * @param DeepCopy                $deepCopyCloner
     * @param int|null                $retryTimeout
     */
    public function __construct(
        ProducerInterface $originalQueueProducer,
        CircuitBreakerInterface $circuitBreaker,
        DeepCopy $deepCopyCloner,
        ?int $retryTimeout = null
    ) {
        $this->originalQueueProducer = $originalQueueProducer;
        $this->originalQueueProducerClone = $deepCopyCloner->copy($originalQueueProducer);
        $this->circuitBreaker = $circuitBreaker;
        $this->deepCopyCloner = $deepCopyCloner;
        $this->retryTimeout = $retryTimeout ?? self::DEFAULT_RETRY_TIMEOUT;
    }

    /**
     * The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendEvent.
     *
     * @param string                 $topic
     * @param string|mixed[]|Message $message
     *
     * @throws MessageWasNotSentToQueueException
     */
    public function sendEvent(string $topic, $message): void
    {
        $callback = static function (ProducerInterface $producer) use ($topic, $message): void {
            $producer->sendEvent($topic, $message);
        };
        $this->runFaultTolerantProcess($callback);
    }

    /**
     * The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendCommand.
     * The promise is returned if needReply argument is true.
     *
     * @param string                 $command
     * @param string|mixed[]|Message $message
     * @param bool                   $needReply
     *
     * @return Promise|null
     *
     * @throws LoggerException
     * @throws MessageWasNotSentToQueueException
     *
     * @SuppressWarnings(PHPMD)
     */
    public function sendCommand(string $command, $message, bool $needReply = false): ?Promise
    {
        $callback = static function (ProducerInterface $producer) use ($command, $message, $needReply): ?Promise {
            return $producer->sendCommand($command, $message, $needReply);
        };

        return $this->runFaultTolerantProcess($callback);
    }

    /**
     * Fault tolerant consume the queue.
     *
     * @param Closure $callback
     *
     * @return mixed
     *
     * @throws MessageWasNotSentToQueueException
     * @throws LoggerException
     *
     * @SuppressWarnings(PHPMD)
     */
    protected function runFaultTolerantProcess(Closure $callback)
    {
        $resetConnection = false;
        $lastException = false;

        do {
            $exception = false;

            while ($this->circuitBreaker->isAvailable(self::ENQUEUE_PRODUCER_SERVICE_NAME)) {
                try {
                    if ($resetConnection) {
                        $this->resetConnection();
                    }

                    $result = $callback($this->originalQueueProducer);

                    // Successful execution - recover circuit breaker.
                    $this->circuitBreaker->reportSuccess(self::ENQUEUE_PRODUCER_SERVICE_NAME);

                    return $result;
                } catch (Throwable $exception) {
                    $this->circuitBreaker->reportFailure(self::ENQUEUE_PRODUCER_SERVICE_NAME);
                    $resetConnection = true;
                    $lastException = $exception;
                }
            }

            if ($exception && $exception instanceof Throwable) {
                $this->logMessage($this->getExceptionMessage($exception), LOG_WARNING);
            }

            if ($this->circuitBreaker->isBlocked(self::ENQUEUE_PRODUCER_SERVICE_NAME)) {
                break;
            }
            $this->sleep();
        } while (true);

        if ($lastException && $lastException instanceof Throwable) {
            throw new MessageWasNotSentToQueueException($lastException->getMessage(), 0, $lastException);
        }

        throw new MessageWasNotSentToQueueException('Service has been blocked.');
    }

    /**
     * Force-reset the AMQP connection for reconnection.
     *
     * Four-layer reset:
     * 1. DeepCopy the pristine producer clone (restores object graph state)
     * 2. Walk into the AmqpContext via the driver
     * 3. Try graceful context.close() (swallow exception on dead TCP)
     * 4. Null the bunnyChannel on AmqpContext (forces factory recreation)
     * 5. Null the cached BunnyClient on AmqpConnectionFactory (forces fresh TCP)
     *
     * Without steps 4-5, the factory returns the cached dead client and the
     * reconnect fails immediately even after DeepCopy. The factory is accessed
     * via ReflectionFunction on the channel factory closure bound to the factory.
     */
    protected function resetConnection(): void
    {
        // Step 1: Restore producer from pristine clone (resets internal counters, etc).
        $this->originalQueueProducer = $this->deepCopyCloner->copy($this->originalQueueProducerClone);

        // Steps 2-5: Reach into AmqpContext and do the real reset (DeepCopy alone is insufficient
        // because the bunny TCP socket state lives inside cached closures/reflection-reachable state).
        try {
            $context = $this->resolveAmqpContext($this->originalQueueProducer);

            if ($context === null) {
                return;
            }

            // Step 3: Try graceful close (swallow exception on dead TCP).
            try {
                $context->close();
            } catch (Throwable $e) {
                $this->logMessage(
                    'Connection close failed during reset (expected on dead TCP): ' . $e->getMessage(),
                    LOG_INFO
                );
            }

            // Step 4: Null the channel so getBunnyChannel() invokes the factory.
            $this->setPrivate($context, self::ENQUEUE_CONTEXT_CHANNEL_PROPERTY_NAME)(null);

            // Step 5: Null the cached BunnyClient on AmqpConnectionFactory.
            $factory = $this->resolveConnectionFactory($context);
            if ($factory !== null) {
                $this->setPrivate($factory, 'client')(null);
                $this->logMessage(
                    'Producer connection factory client reset - next publish will create fresh TCP',
                    LOG_INFO
                );
            }
        } catch (Throwable $e) {
            $this->logMessage('Full producer connection reset failed: ' . $e->getMessage(), LOG_WARNING);
        }
    }

    /**
     * Walk the producer chain to find the AmqpContext.
     *
     * The Enqueue Producer holds a DriverInterface internally (protected $driver).
     * DriverInterface::getContext() returns the transport context (AmqpContext for amqp).
     *
     * For TraceableProducer wrappers, we unwrap via $this->producer first.
     */
    private function resolveAmqpContext(ProducerInterface $producer): ?object
    {
        $target = $producer;

        // Unwrap TraceableProducer: holds the real Producer as $this->producer.
        try {
            $inner = $this->getPrivate($target, 'producer')();
            if ($inner instanceof ProducerInterface) {
                $target = $inner;
            }
        } catch (Throwable) {
            // Not a TraceableProducer; $target is already the real Producer.
        }

        // Real Producer holds DriverInterface as $this->driver.
        try {
            $driver = $this->getPrivate($target, 'driver')();

            if (! is_object($driver) || ! method_exists($driver, 'getContext')) {
                return null;
            }

            $context = $driver->getContext();

            return is_object($context) ? $context : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Resolve the AmqpConnectionFactory from the context channel factory closure.
     *
     * AmqpConnectionFactory::createContext() passes a closure bound to $this (the factory)
     * into AmqpContext as the $bunnyChannelFactory. We use ReflectionFunction::getClosureThis()
     * to extract the factory instance, then null its $client property.
     */
    private function resolveConnectionFactory(object $context): ?object
    {
        try {
            $channelFactory = $this->getPrivate($context, self::ENQUEUE_CONTEXT_CHANNEL_FACTORY_PROPERTY_NAME)();

            if ($channelFactory instanceof Closure) {
                $reflection = new \ReflectionFunction($channelFactory);
                $boundObject = $reflection->getClosureThis();

                if ($boundObject !== null) {
                    return $boundObject;
                }
            }
        } catch (Throwable) {
            // Factory not accessible - channel not lazy, or reflection failed.
        }

        return null;
    }

    /**
     * Sleep after failure.
     */
    protected function sleep(): void
    {
        usleep($this->retryTimeout);
    }
}
