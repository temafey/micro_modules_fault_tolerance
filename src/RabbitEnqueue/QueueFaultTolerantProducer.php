<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\RabbitEnqueue;

use AdgoalCommon\Base\Domain\Exception\LoggerException;
use AdgoalCommon\Base\Utils\LoggerTrait;
use AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreakerInterface;
use AdgoalCommon\FaultTolerance\RabbitEnqueue\Exception\MessageWasNotSentToQueueException;
use Closure;
use DeepCopy\DeepCopy;
use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use Enqueue\Rpc\Promise;
use Throwable;

/**
 * Class QueueFaultTolerantProducer.
 */
class QueueFaultTolerantProducer implements ProducerInterface
{
    use LoggerTrait;

    public const ENQUEUE_PRODUCER_SERVICE_NAME = 'enqueue_producer';
    protected const DEFAULT_RETRY_TIMEOUT = 1000000;

    /**
     * Queue producer.
     *
     * @var ProducerInterface
     */
    protected $originalQueueProducer;

    /**
     * Clone for queue producer.
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

                    return $callback($this->originalQueueProducer);
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
     * Find and return first active queue consumer from queue channel.
     */
    protected function resetConnection(): void
    {
        $this->originalQueueProducer = $this->deepCopyCloner->copy($this->originalQueueProducerClone);
    }

    /**
     * Sleep after failure.
     */
    protected function sleep(): void
    {
        usleep($this->retryTimeout);
    }
}
