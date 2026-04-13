<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\RabbitEnqueue;

use MicroModule\Base\Domain\Exception\LoggerException;
use MicroModule\Base\Utils\LoggerTrait;
use MicroModule\FaultTolerance\CircuitBreaker\CircuitBreakerInterface;
use MicroModule\FaultTolerance\RabbitEnqueue\Exception\QueueFaultTolerantConsumerException;
use Closure;
use Enqueue\Consumption\ExtensionInterface;
use Enqueue\Consumption\QueueConsumerInterface;
use Exception;
use Interop\Queue\Context;
use Interop\Queue\Processor;
use Interop\Queue\Queue as InteropQueue;
use Throwable;

/**
 * Fault-tolerant wrapper for Enqueue QueueConsumer.
 *
 * Handles TCP connection loss to RabbitMQ by:
 * 1. Catching socket/connection exceptions from bunny/bunny
 * 2. Force-resetting the AMQP connection (channel + client + factory cache)
 * 3. Retrying with exponential backoff
 * 4. Reporting success to circuit breaker to allow recovery
 *
 * The reset mechanism must null three layers:
 * - AmqpContext.$bunnyChannel (the dead channel)
 * - AmqpConnectionFactory.$client (the cached BunnyClient with dead TCP)
 * - This forces establishConnection() to create a fresh BunnyClient + connect()
 */
class QueueFaultTolerantConsumer implements QueueConsumerInterface
{
    use PrivateTrait, LoggerTrait;

    public const ENQUEUE_CONSUMER_SERVICE_NAME = 'enqueue_consumer';
    protected const DEFAULT_RETRY_TIMEOUT = 100000;
    protected const MAX_BACKOFF_TIMEOUT = 30_000_000; // 30 seconds max backoff
    protected const CONTEXT_QUEUE_CONTEXT_protected_PROPERTY_NAME = 'interopContext';
    protected const ENQUEUE_CONTEXT_CHANNEL_protected_PROPERTY_NAME = 'bunnyChannel';

    /**
     * Original QueueConsumer object.
     */
    protected QueueConsumerInterface $originalQueueConsumer;

    /**
     * Circuit breaker counts each failure and once you reach limit it will skip connection attempt with instant failure.
     */
    protected CircuitBreakerInterface $circuitBreaker;

    /**
     * Connection timeout retry in microseconds.
     */
    protected int $retryTimeout;

    /**
     * QueueFaultTolerantConsumer constructor.
     */
    public function __construct(
        QueueConsumerInterface $originalQueueConsumer,
        CircuitBreakerInterface $circuitBreaker,
        ?int $retryTimeout = self::DEFAULT_RETRY_TIMEOUT
    ) {
        $this->originalQueueConsumer = $originalQueueConsumer;
        $this->circuitBreaker = $circuitBreaker;
        $this->retryTimeout = $retryTimeout ?? self::DEFAULT_RETRY_TIMEOUT;
    }

    /**
     * Set receive timeout in milliseconds.
     */
    public function setReceiveTimeout(int $timeout): void
    {
        $this->originalQueueConsumer->setReceiveTimeout($timeout);
    }

    /**
     * In milliseconds.
     */
    public function getReceiveTimeout(): int
    {
        return $this->originalQueueConsumer->getReceiveTimeout();
    }

    /**
     * Return Queue Context object.
     */
    public function getContext(): Context
    {
        return $this->originalQueueConsumer->getContext();
    }

    /**
     * Bind enqueue processor by queue name.
     */
    public function bind($queueName, Processor $processor): QueueConsumerInterface
    {
        $this->originalQueueConsumer->bind($queueName, $processor);

        return $this;
    }

    /**
     * Bind enqueue callback by queue name.
     */
    public function bindCallback($queueName, callable $processor): QueueConsumerInterface
    {
        $this->originalQueueConsumer->bindCallback($queueName, $processor);

        return $this;
    }

    /**
     * Runtime extension - is an extension or a collection of extensions which could be set on runtime.
     *
     * @throws Exception
     */
    public function consume(?ExtensionInterface $runtimeExtension = null): void
    {
        $callback = static function (QueueConsumerInterface $originalQueueConsumer) use ($runtimeExtension): void {
            $originalQueueConsumer->consume($runtimeExtension);
        };
        $this->runFaultTolerantProcess($callback);
    }

    /**
     * Fault tolerant consume the queue.
     *
     * Wraps the original consumer's consume() call with:
     * - Circuit breaker integration (failure counting + blocking)
     * - Automatic TCP reconnection on failure via resetConnection()
     * - Exponential backoff between retries
     * - Success reporting to recover circuit breaker state
     *
     * @throws QueueFaultTolerantConsumerException
     * @throws LoggerException
     *
     * @SuppressWarnings(PHPMD)
     */
    protected function runFaultTolerantProcess(Closure $callback): mixed
    {
        $resetConnection = false;
        $lastException = false;
        $retryCount = 0;

        do {
            $exception = false;

            while ($this->circuitBreaker->isAvailable(self::ENQUEUE_CONSUMER_SERVICE_NAME)) {
                try {
                    if ($resetConnection) {
                        $this->resetConnection();
                    }

                    $result = $callback($this->originalQueueConsumer);

                    // Successful execution - recover circuit breaker
                    $this->circuitBreaker->reportSuccess(self::ENQUEUE_CONSUMER_SERVICE_NAME);
                    $retryCount = 0;

                    return $result;
                } catch (Throwable $exception) {
                    $this->circuitBreaker->reportFailure(self::ENQUEUE_CONSUMER_SERVICE_NAME);
                    $resetConnection = true;
                    $lastException = $exception;
                    ++$retryCount;
                }
            }

            if ($exception && $exception instanceof Throwable) {
                $this->logMessage($this->getExceptionMessage($exception), LOG_WARNING);
            }

            if ($this->circuitBreaker->isBlocked(self::ENQUEUE_CONSUMER_SERVICE_NAME)) {
                break;
            }
            $this->sleepWithBackoff($retryCount);
        } while (true);

        if ($lastException && $lastException instanceof Throwable) {
            throw new QueueFaultTolerantConsumerException($lastException->getMessage(), 0, $lastException);
        }

        throw new QueueFaultTolerantConsumerException('Service has been blocked.');
    }

    /**
     * Force-reset the AMQP connection for reconnection.
     *
     * Three-layer reset:
     * 1. Try graceful context.close() - may throw on dead TCP, that is OK
     * 2. Null the bunnyChannel on AmqpContext - forces factory recreation
     * 3. Null the cached BunnyClient on AmqpConnectionFactory - forces fresh TCP
     *
     * Without step 3, the factory returns the cached dead client and the reconnect
     * fails immediately. The factory is accessed via ReflectionFunction on the
     * channel factory closure which is bound to the AmqpConnectionFactory instance.
     */
    protected function resetConnection(): void
    {
        try {
            /** @var Context $context */
            $context = $this->getPrivate(
                $this->originalQueueConsumer,
                self::CONTEXT_QUEUE_CONTEXT_protected_PROPERTY_NAME
            )();

            // Step 1: Try graceful close (swallow exception on dead TCP)
            try {
                $context->close();
            } catch (Throwable $e) {
                $this->logMessage(
                    'Connection close failed during reset (expected on dead TCP): ' . $e->getMessage(),
                    LOG_INFO
                );
            }

            // Step 2: Null the channel so getBunnyChannel() calls the factory
            $this->setPrivate($context, self::ENQUEUE_CONTEXT_CHANNEL_protected_PROPERTY_NAME)(null);

            // Step 3: Null the cached BunnyClient on AmqpConnectionFactory
            $factory = $this->resolveConnectionFactory($context);
            if ($factory !== null) {
                $this->setPrivate($factory, 'client')(null);
                $this->logMessage('Connection factory client reset - next consume will create fresh TCP', LOG_INFO);
            }
        } catch (Throwable $e) {
            $this->logMessage('Full connection reset failed: ' . $e->getMessage(), LOG_WARNING);
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
            $channelFactory = $this->getPrivate($context, 'bunnyChannelFactory')();

            if ($channelFactory instanceof Closure) {
                $reflection = new \ReflectionFunction($channelFactory);
                $boundObject = $reflection->getClosureThis();

                if ($boundObject !== null) {
                    return $boundObject;
                }
            }
        } catch (Throwable) {
            // Factory not accessible - channel is not lazy, or reflection failed.
            // Non-lazy contexts (Channel injected directly) do not have a factory closure.
        }

        return null;
    }

    /**
     * Sleep with exponential backoff between retry attempts.
     *
     * Backoff formula: retryTimeout * 2^retryCount, capped at MAX_BACKOFF_TIMEOUT.
     * Example with default 100ms base: 100ms, 200ms, 400ms, 800ms, ..., 30s cap
     */
    protected function sleepWithBackoff(int $retryCount): void
    {
        $backoff = (int) min(
            $this->retryTimeout * (2 ** min($retryCount, 10)),
            self::MAX_BACKOFF_TIMEOUT
        );
        usleep($backoff);
    }

    /**
     * Sleep after failure (backward-compatible entry point).
     */
    protected function sleep(): void
    {
        $this->sleepWithBackoff(0);
    }
}
