<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\RabbitEnqueue;

use MicroModule\Base\Utils\LoggerTrait;
use AMQPChannel;
use AMQPConnectionException;
use AMQPQueue;
use AMQPQueueException;
use Closure;
use Enqueue\AmqpExt\AmqpContext;
use Enqueue\AmqpExt\AmqpSubscriptionConsumer;
use Enqueue\Consumption\Context\PreConsume;
use Enqueue\Consumption\PreConsumeExtensionInterface;
use Interop\Queue\SubscriptionConsumer;
use Throwable;

/**
 * Class QueueFaultTolerantConsumer.
 */
class QueueFaultTolerantConsumer implements PreConsumeExtensionInterface
{
    use LoggerTrait;

    private const DEFAULT_RETRY_TIMEOUT = 1000000;
    private const DEFAULT_RETRY_ATTEMPTS = 3;
    private const DEFAULT_HEALTH_CHECK_EVENTS_COUNT = 10;

    private const CONTEXT_QUEUE_CHANNEL_PRIVATE_PROPERTY_NAME = 'extChannel';
    private const SUBSCRIPTION_CONSUMER_SUBSCRIBERS_PRIVATE_PROPERTY_NAME = 'subscribers';

    private const CONNECTION_HEALTH_CHECK_STATUS_CONNECTED = 1;
    private const CONNECTION_HEALTH_CHECK_STATUS_RECONNECTED = 2;

    /**
     * QueueFaultTolerantConsumer constructor.
     *
     * @param int|null $retryAttempts
     * @param int|null $retryTimeout
     * @param int|null $healthCheckEventsCount
     */
    public function __construct(
        protected ?int $retryAttempts = self::DEFAULT_RETRY_ATTEMPTS,
        protected ?int $retryTimeout = self::DEFAULT_RETRY_TIMEOUT,
        protected ?int $healthCheckEventsCount = self::DEFAULT_HEALTH_CHECK_EVENTS_COUNT
    ) {}

    /**
     * Executed at every new cycle before calling SubscriptionConsumer::consume method.
     * The consumption could be interrupted at this step.
     *
     * @param PreConsume $context
     *
     * @throws Throwable
     * @throws AMQPConnectionException
     * @throws AMQPQueueException
     */
    public function onPreConsume(PreConsume $context): void
    {
        ++$this->eventsCounter;

        if ($this->eventsCounter !== $this->healthCheckEventsCount) {
            return;
        }
        $this->eventsCounter = 0;

        /** @var AmqpContext $queueContext */
        $queueContext = $context->getContext();
        $ampQueue = $this->getAMQPQueue($queueContext);
        $healthStatus = $this->runFaultTolerantProcess($ampQueue, $queueContext);

        if (self::CONNECTION_HEALTH_CHECK_STATUS_CONNECTED === $healthStatus) {
            return;
        }

        $subscriptionConsumer = $context->getSubscriptionConsumer();
        $this->updateSubscriptionConsumerSubscribers($subscriptionConsumer);
    }

    /**
     * Find and return first active queue consumer from queue channel.
     *
     * @param AmqpContext $queueContext
     *
     * @return AMQPQueue
     */
    private function getAMQPQueue(AmqpContext $queueContext): AMQPQueue
    {
        /** @var AMQPChannel $queueChannel */
        $queueChannel = $queueContext->getExtChannel();
        $consumers = $queueChannel->getConsumers();
        reset($consumers);

        return current($consumers);
    }

    /**
     * Queue connection health check with retry.
     *
     * @param AMQPQueue   $ampQueue
     * @param AmqpContext $queueContext
     *
     * @return int
     *
     * @throws Throwable
     * @throws AMQPConnectionException
     * @throws AMQPQueueException
     */
    private function runFaultTolerantProcess(AMQPQueue $ampQueue, AmqpContext $queueContext): int
    {
        $retryAttempt = 0;
        $status = self::CONNECTION_HEALTH_CHECK_STATUS_CONNECTED;
        $consumerException = null;

        do {
            try {
                $ampQueue->consume(null, AMQP_PASSIVE);
                $ampQueue->cancel();

                return $status;
            } catch (Throwable $consumerException) {
                $queueContext->close();
                $this->logMessage($this->getExceptionMessage($consumerException), LOG_WARNING);
                usleep($this->retryTimeout);
                ++$retryAttempt;
                /** @var AMQPChannel $queueChannel */
                $queueChannel = $queueContext->getExtChannel();

                if (!$queueChannel->isConnected()) {
                    $ampQueue = $this->createAMQPQueue($ampQueue->getName(), $queueContext);
                }
                $status = self::CONNECTION_HEALTH_CHECK_STATUS_RECONNECTED;
            }
        } while ($retryAttempt < $this->retryAttempts);

        throw $consumerException;
    }

    /**
     * Create and return new AMPQueue object with new connection.
     *
     * @param string      $queueName
     * @param AmqpContext $queueContext
     *
     * @return AMQPQueue
     *
     * @throws AMQPConnectionException
     * @throws AMQPQueueException
     */
    private function createAMQPQueue(string $queueName, AmqpContext $queueContext): AMQPQueue
    {
        $this->setPrivate($queueContext, self::CONTEXT_QUEUE_CHANNEL_PRIVATE_PROPERTY_NAME)(null);
        $ampQueue = new AMQPQueue($queueContext->getExtChannel());
        $ampQueue->setName($queueName);

        return $ampQueue;
    }

    /**
     * Update queue subscribers through AmqpSubscriptionConsumer object.
     *
     * @param SubscriptionConsumer $subscriptionConsumer
     */
    private function updateSubscriptionConsumerSubscribers(SubscriptionConsumer $subscriptionConsumer): void
    {
        $subscribers = $this->getPrivate($subscriptionConsumer, self::SUBSCRIPTION_CONSUMER_SUBSCRIBERS_PRIVATE_PROPERTY_NAME)();
        $this->setPrivate($subscriptionConsumer, self::SUBSCRIPTION_CONSUMER_SUBSCRIBERS_PRIVATE_PROPERTY_NAME)([]);

        foreach ($subscribers as $subscriber) {
            $subscriptionConsumer->subscribe($subscriber[0], $subscriber[1]);
        }
    }

    /**
     * Return closure, that can return any private or protected property value from any object.
     *
     * @param object $obj
     * @param string $attribute
     *
     * @return Closure
     */
    private function getPrivate(object $obj, string $attribute): Closure
    {
        $getter = function () use ($attribute) {
            return $this->$attribute;
        };

        return Closure::bind($getter, $obj, get_class($obj));
    }

    /**
     * Return closure ,that can set any private or protected property value in any object.
     *
     * @param object $obj
     * @param string $attribute
     *
     * @return Closure
     */
    private function setPrivate(object $obj, string $attribute): Closure
    {
        $setter = function ($value) use ($attribute): void {
            $this->$attribute = $value;
        };

        return Closure::bind($setter, $obj, get_class($obj));
    }
}
