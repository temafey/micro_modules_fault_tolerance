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
 * Class QueueFaultTolerantConsumer.
 */
class QueueFaultTolerantConsumer implements QueueConsumerInterface
{
    use protectedTrait, LoggerTrait;

    public const ENQUEUE_CONSUMER_SERVICE_NAME = 'enqueue_consumer';
    protected const DEFAULT_RETRY_TIMEOUT = 100000;
    protected const CONTEXT_QUEUE_CONTEXT_protected_PROPERTY_NAME = 'interopContext';
    protected const ENQUEUE_CONTEXT_CHANNEL_protected_PROPERTY_NAME = 'channel';

    /**
     * Original QueueConsumer object.
     *
     * @var QueueConsumerInterface
     */
    protected $originalQueueConsumer;

    /**
     * Circuit breaker counts each failure and once you reach limit it will skip connection attempt with instant failure.
     *
     * @var CircuitBreakerInterface
     */
    protected $circuitBreaker;

    /**
     * Connection timeout retry in microsecond.
     *
     * @var int
     */
    protected $retryTimeout;

    /**
     * QueueFaultTolerantConsumer constructor.
     *
     * @param QueueConsumerInterface  $originalQueueConsumer
     * @param CircuitBreakerInterface $circuitBreaker
     * @param int|null                $retryTimeout
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
     * Set receive timeout.
     *
     * In milliseconds.
     *
     * @param int $timeout
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
     *
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->originalQueueConsumer->getContext();
    }

    /**
     * Bind enqueue processor by queue name.
     *
     * @param InteropQueue|string $queueName
     * @param Processor           $processor
     *
     * @return QueueConsumerInterface
     */
    public function bind($queueName, Processor $processor): QueueConsumerInterface
    {
        $this->originalQueueConsumer->bind($queueName, $processor);

        return $this;
    }

    /**
     * Bind enqueue callback by queue name.
     *
     * @param InteropQueue|string $queueName
     * @param callable            $processor
     *
     * @return QueueConsumerInterface
     */
    public function bindCallback($queueName, callable $processor): QueueConsumerInterface
    {
        $this->originalQueueConsumer->bindCallback($queueName, $processor);

        return $this;
    }

    /**
     * Runtime extension - is an extension or a collection of extensions which could be set on runtime.
     * Here's a good example: @see LimitsExtensionsCommandTrait.
     *
     * @param ExtensionInterface|null $runtimeExtension
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
     * @param Closure $callback
     *
     * @return mixed
     *
     * @throws QueueFaultTolerantConsumerException
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

            while ($this->circuitBreaker->isAvailable(self::ENQUEUE_CONSUMER_SERVICE_NAME)) {
                try {
                    if ($resetConnection) {
                        $this->resetConnection();
                    }

                    return $callback($this->originalQueueConsumer);
                } catch (Throwable $exception) {
                    $this->circuitBreaker->reportFailure(self::ENQUEUE_CONSUMER_SERVICE_NAME);
                    $resetConnection = true;
                    $lastException = $exception;
                }
            }

            if ($exception && $exception instanceof Throwable) {
                $this->logMessage($this->getExceptionMessage($exception), LOG_WARNING);
            }

            if ($this->circuitBreaker->isBlocked(self::ENQUEUE_CONSUMER_SERVICE_NAME)) {
                break;
            }
            $this->sleep();
        } while (true);

        if ($lastException && $lastException instanceof Throwable) {
            throw new QueueFaultTolerantConsumerException($lastException->getMessage(), 0, $lastException);
        }

        throw new QueueFaultTolerantConsumerException('Service has been blocked.');
    }

    /**
     * Find and return first active queue consumer from queue channel.
     */
    protected function resetConnection(): void
    {
        /** @var Context $context */
        $context = $this->getprotected($this->originalQueueConsumer, self::CONTEXT_QUEUE_CONTEXT_protected_PROPERTY_NAME)();
        $context->close();
        $this->setprotected($context, self::ENQUEUE_CONTEXT_CHANNEL_protected_PROPERTY_NAME)(null);
    }

    /**
     * Sleep after failure.
     */
    protected function sleep(): void
    {
        usleep($this->retryTimeout);
    }
}
