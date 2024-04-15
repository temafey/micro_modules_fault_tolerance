<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\RabbitEnqueue;

use AdgoalCommon\Base\Domain\Exception\LoggerException;
use AdgoalCommon\Base\Utils\LoggerTrait;
use AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreakerInterface;
use AdgoalCommon\FaultTolerance\RabbitEnqueue\Exception\QueueFaultTolerantRouterProcessorException;
use Closure;
use Enqueue\Client\Driver\AmqpDriver;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use Throwable;

/**
 * Class QueueFaultTolerantRouterProcessor.
 */
class QueueFaultTolerantRouterProcessor implements Processor
{
    use PrivateTrait, LoggerTrait;

    public const ENQUEUE_ROUTER_PROCESSOR_SERVICE_NAME = 'router_processor';
    private const DEFAULT_RETRY_TIMEOUT = 100000;
    private const ENQUEUE_ROUTER_PROCEOR_DRIVER_PRIVATE_PROPERTY_NAME = 'driver';
    private const ENQUEUE_CONTEXT_CHANNEL_PRIVATE_PROPERTY_NAME = 'channel';

    /**
     * Original Processor object.
     *
     * @var Processor
     */
    private $originalRouterProcessor;

    /**
     * Circuit breaker counts each failure and once you reach limit it will skip connection attempt with instant failure.
     *
     * @var CircuitBreakerInterface
     */
    private $circuitBreaker;

    /**
     * Connection timeout retry in microsecond.
     *
     * @var int
     */
    private $retryTimeout;

    /**
     * QueueFaultRouterProcessor constructor.
     *
     * @param Processor               $originalRouterProcessor
     * @param CircuitBreakerInterface $circuitBreaker
     * @param int|null                $retryTimeout
     */
    public function __construct(
        Processor $originalRouterProcessor,
        CircuitBreakerInterface $circuitBreaker,
        ?int $retryTimeout = null
    ) {
        $this->originalRouterProcessor = $originalRouterProcessor;
        $this->circuitBreaker = $circuitBreaker;
        $this->retryTimeout = $retryTimeout ?? self::DEFAULT_RETRY_TIMEOUT;
    }

    /**
     * The method has to return either self::ACK, self::REJECT, self::REQUEUE string.
     *
     * The method also can return an object.
     * It must implement __toString method and the method must return one of the constants from above.
     *
     * @param Message $message
     * @param Context $context
     *
     * @return string|object with __toString method implemented
     */
    public function process(Message $message, Context $context)
    {
        $callback = static function (Processor $originalRouterProcessor) use ($message, $context) {
            return $originalRouterProcessor->process($message, $context);
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
     * @throws QueueFaultTolerantRouterProcessorException
     * @throws LoggerException
     *
     * @SuppressWarnings(PHPMD)
     */
    private function runFaultTolerantProcess(Closure $callback)
    {
        $resetConnection = false;
        $lastException = false;

        do {
            $exception = false;

            while ($this->circuitBreaker->isAvailable(self::ENQUEUE_ROUTER_PROCESSOR_SERVICE_NAME)) {
                try {
                    if ($resetConnection) {
                        $this->resetConnection();
                    }

                    return $callback($this->originalRouterProcessor);
                } catch (Throwable $exception) {
                    $this->circuitBreaker->reportFailure(self::ENQUEUE_ROUTER_PROCESSOR_SERVICE_NAME);
                    $resetConnection = true;
                    $lastException = $exception;
                }
            }

            if ($exception && $exception instanceof Throwable) {
                $this->logMessage($this->getExceptionMessage($exception), LOG_WARNING);
            }

            if ($this->circuitBreaker->isBlocked(self::ENQUEUE_ROUTER_PROCESSOR_SERVICE_NAME)) {
                break;
            }
            $this->sleep();
        } while (true);

        if ($lastException && $lastException instanceof Throwable) {
            throw new QueueFaultTolerantRouterProcessorException($lastException->getMessage(), 0, $lastException);
        }

        throw new QueueFaultTolerantRouterProcessorException('Service has been blocked.');
    }

    /**
     * Find and return first active queue consumer from queue channel.
     */
    private function resetConnection(): void
    {
        /** @var AmqpDriver $driver */
        $driver = $this->getPrivate($this->originalRouterProcessor, self::ENQUEUE_ROUTER_PROCEOR_DRIVER_PRIVATE_PROPERTY_NAME)();
        $context = $driver->getContext();
        $context->close();
        $this->setPrivate($context, self::ENQUEUE_CONTEXT_CHANNEL_PRIVATE_PROPERTY_NAME)(null);
    }

    /**
     * Sleep after failure.
     */
    private function sleep(): void
    {
        usleep($this->retryTimeout);
    }
}
