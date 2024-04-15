<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\RabbitEnqueue;

use MicroModule\Base\Utils\LoggerTrait;
use MicroModule\FaultTolerance\RabbitEnqueue\Exception\MessageWasNotSentToQueueException;
use AMQPConnectionException;
use Closure;
use DeepCopy\DeepCopy;
use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use Enqueue\Rpc\Promise;
use Interop\Queue\Exception\Exception as InteropQueueException;

/**
 * Class QueueFaultTolerantProducer.
 */
class QueueFaultTolerantProducer implements ProducerInterface
{
    use LoggerTrait;

    private const DEFAULT_RETRY_TIMEOUT = 1000000;
    private const DEFAULT_RETRY_ATTEMPTS = 3;

    /**
     * Clone for queue producer.
     *
     * @var ProducerInterface
     */
    private $queueProducerClone;

    /**
     * ProgramResultRepository constructor.
     *
     * @param ProducerInterface $queueProducer
     * @param DeepCopy          $deepCopyCloner
     * @param int|null          $retryAttempts
     * @param int|null          $retryTimeout
     */
    public function __construct(
        protected roducerInterface $queueProducer,
        protected DeepCopy $deepCopyCloner,
        protected ?int $retryAttempts = self::DEFAULT_RETRY_TIMEOUT,
        protected ?int $retryTimeout = self::DEFAULT_RETRY_TIMEOUT
    ) {
        $this->queueProducerClone = $deepCopyCloner->copy($queueProducer);
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
     * @throws MessageWasNotSentToQueueException
     *
     * @SuppressWarnings(PHPMD)
     */
    public function sendCommand(string $command, $message, bool $needReply = false): ?Promise
    {
        $callback = static function (ProducerInterface $producer) use ($command, $message, $needReply) {
            return $producer->sendCommand($command, $message, $needReply);
        };

        return $this->runFaultTolerantProcess($callback);
    }

    /**
     * Fault tolerant sending message to queue.
     *
     * @param Closure $callback
     *
     * @return mixed
     *
     * @throws MessageWasNotSentToQueueException
     */
    protected function runFaultTolerantProcess(Closure $callback)
    {
        $retryAttempt = 0;

        do {
            try {
                return $callback($this->queueProducer);
            } catch (AMQPConnectionException | InteropQueueException $e) {
                usleep($this->retryTimeout);
                ++$retryAttempt;
                $this->queueProducer = $this->deepCopyCloner->copy($this->queueProducerClone);
                $this->logMessage($this->getExceptionMessage($e), LOG_WARNING);
            }
        } while ($retryAttempt < $this->retryAttempts);

        /* @psalm-suppress PossiblyUndefinedVariable */
        throw new MessageWasNotSentToQueueException($e->getMessage(), 0, $e);
    }
}
