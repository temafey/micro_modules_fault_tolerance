<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\RabbitEnqueue;

use AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreakerInterface;
use Enqueue\Consumption\Context\PostMessageReceived;
use Enqueue\Consumption\Context\ProcessorException;
use Enqueue\Consumption\PostMessageReceivedExtensionInterface;
use Enqueue\Consumption\ProcessorExceptionExtensionInterface;

/**
 * Class QueueFaultTolerantExtensionRetry.
 */
class QueueFaultTolerantExtensionRetry implements PostMessageReceivedExtensionInterface, ProcessorExceptionExtensionInterface
{
    /**
     * Circuit breaker counts each failure and once you reach limit it will skip connection attempt with instant failure.
     *
     * @var CircuitBreakerInterface
     */
    private $circuitBreaker;

    /**
     * Configure instance with storage implementation and default threshold and retry timeout.
     *
     * @param CircuitBreakerInterface $circuitBreaker
     */
    public function __construct(
        CircuitBreakerInterface $circuitBreaker
    ) {
        $this->circuitBreaker = $circuitBreaker;
    }

    /**
     * Execute if a processor throws an exception.
     * The result could be set, if result is not set the exception is thrown again.
     *
     * @param ProcessorException $context
     */
    public function onProcessorException(ProcessorException $context): void
    {
        $this->circuitBreaker->reportFailure(QueueFaultTolerantConsumer::ENQUEUE_CONSUMER_SERVICE_NAME);
    }

    /**
     * Executed at the very end of consumption callback. The message has already been acknowledged.
     * The message result could not be changed.
     * The consumption could be interrupted at this point.
     */
    public function onPostMessageReceived(PostMessageReceived $context): void
    {
        $this->circuitBreaker->reportSuccess(QueueFaultTolerantConsumer::ENQUEUE_CONSUMER_SERVICE_NAME);
    }
}
