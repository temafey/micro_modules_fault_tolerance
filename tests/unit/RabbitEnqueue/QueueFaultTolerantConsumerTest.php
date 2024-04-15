<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Tests\Unit\RabbitEnqueue;

use MicroModule\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer;
use MicroModule\FaultTolerance\Tests\Unit\RepositoryTestCase;
use AMQPChannel;
use AMQPConnectionException;
use AMQPQueue;
use AMQPQueueException;
use Enqueue\AmqpExt\AmqpContext;
use Enqueue\AmqpExt\AmqpSubscriptionConsumer;
use Enqueue\Consumption\Context\PreConsume;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class QueueFaultTolerantConsumerTest.
 *
 * @category Tests\Unit\Infrastructure\Service
 */
class QueueFaultTolerantConsumerTest extends RepositoryTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::onPreConsume
     *
     * @dataProvider \MicroModule\FaultTolerance\Tests\Unit\DataProvider\QueueFaultTolerantConsumerProvider::getNoRetryCases()
     *
     * @param mixed[] $times
     * @param int     $retryTimeout
     *
     * @throws AMQPConnectionException
     * @throws AMQPQueueException
     * @throws Throwable
     */
    public function ifConnectionToQueueExistsNoRetryTest(array $times, int $retryTimeout): void
    {
        $healthCheck = 1;
        $queueFaultTolerantConsumer = new QueueFaultTolerantConsumer($times['retryAttempts'], $retryTimeout, $healthCheck);
        $loggerMock = $this->makeLoggerMock($times);
        $queueFaultTolerantConsumer->setLogger($loggerMock);

        $preConsumer = $this->makePreConsumer(false, $times);
        $queueFaultTolerantConsumer->onPreConsume($preConsumer);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::onPreConsume
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::getAMQPQueue
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::runFaultTolerantProcess
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::updateSubscriptionConsumerSubscribers
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::getPrivate
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::setPrivate
     *
     * @dataProvider \MicroModule\FaultTolerance\Tests\Unit\DataProvider\QueueFaultTolerantConsumerProvider::getRetryCases()
     *
     * @param mixed[] $times
     * @param int     $retryTimeout
     *
     * @throws AMQPConnectionException
     * @throws AMQPQueueException
     * @throws Throwable
     */
    public function connectionToQueueExistsAfterNextRetryTest(array $times, int $retryTimeout): void
    {
        $healthCheck = 1;
        $queueFaultTolerantConsumer = new QueueFaultTolerantConsumer($times['retryAttempts'], $retryTimeout, $healthCheck);
        $loggerMock = $this->makeLoggerMock($times);
        $queueFaultTolerantConsumer->setLogger($loggerMock);

        $preConsumer = $this->makePreConsumer(true, $times);
        $queueFaultTolerantConsumer->onPreConsume($preConsumer);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::onPreConsume
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::runFaultTolerantProcess
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::createAMQPQueue
     *
     * @dataProvider \MicroModule\FaultTolerance\Tests\Unit\DataProvider\QueueFaultTolerantConsumerProvider::getThrowExAfterRetryCases()
     *
     * @param mixed[] $times
     * @param int     $retryTimeout
     *
     * @throws AMQPConnectionException
     * @throws AMQPQueueException
     * @throws Throwable
     */
    public function throwExceptionIfConnectionToQueueNotExistsAfterAllRetryTest(array $times, int $retryTimeout): void
    {
        $this->expectException(AMQPConnectionException::class);

        $healthCheck = 1;
        $queueFaultTolerantConsumer = new QueueFaultTolerantConsumer($times['retryAttempts'], $retryTimeout, $healthCheck);
        $loggerMock = $this->makeLoggerMock($times);
        $queueFaultTolerantConsumer->setLogger($loggerMock);

        $preConsumer = $this->makePreConsumer(true, $times);
        $queueFaultTolerantConsumer->onPreConsume($preConsumer);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantConsumer::onPreConsume
     *
     * @dataProvider \MicroModule\FaultTolerance\Tests\Unit\DataProvider\QueueFaultTolerantConsumerProvider::getHealthCheckCases()
     *
     * @param mixed[] $times
     * @param int     $retryTimeout
     * @param int     $healthCheck
     *
     * @throws AMQPConnectionException
     * @throws Throwable
     * @throws AMQPQueueException
     */
    public function checkConnectionToQueueOneTimeAfterManyEventsTest(array $times, int $retryTimeout, int $healthCheck): void
    {
        $queueFaultTolerantConsumer = new QueueFaultTolerantConsumer($times['retryAttempts'], $retryTimeout, $healthCheck);
        $loggerMock = $this->makeLoggerMock($times);
        $queueFaultTolerantConsumer->setLogger($loggerMock);

        $preConsumer = $this->makePreConsumer(false, $times);

        for ($i = 0; $i < $healthCheck; ++$i) {
            $queueFaultTolerantConsumer->onPreConsume($preConsumer);
        }
    }

    /**
     * Make PreConsumer object.
     *
     * @param bool    $exceptional
     * @param mixed[] $times
     *
     * @return PreConsume
     */
    private function makePreConsumer(bool $exceptional, array $times): PreConsume
    {
        $contextMock = $this->makeContextMock($exceptional, $times);
        $amqpSubscriptionConsumerMock = $this->makeAmqpSubscriptionConsumerMock($times);
        $loggerMock = $this->makeLoggerMock(['warning' => 0]);

        return new PreConsume($contextMock, $amqpSubscriptionConsumerMock, $loggerMock, 1, 1, 0);
    }

    /**
     * Return Context mock object.
     *
     * @param bool    $exceptional
     * @param mixed[] $times
     *
     * @return MockInterface
     */
    private function makeContextMock(bool $exceptional, array $times): MockInterface
    {
        $contextMock = Mockery::mock(AmqpContext::class);
        $AMQPChannelMock = $this->makeAMQPChannelMock($exceptional, $times);
        $contextMock
            ->shouldReceive('getExtChannel')
            ->times($times['getExtChannel'])
            ->andReturn($AMQPChannelMock);
        $contextMock
            ->shouldReceive('close')
            ->times($times['close']);
        $amqpSubscriptionConsumerMock = $this->makeAmqpSubscriptionConsumerMock($times);
        $contextMock->shouldReceive('getSubscriptionConsumer')
            ->times($times['getSubscriptionConsumer'])
            ->andReturn($amqpSubscriptionConsumerMock);

        return $contextMock;
    }

    /**
     * Return AMQPChannel mock object.
     *
     * @param bool    $exceptional
     * @param mixed[] $times
     *
     * @return MockInterface
     */
    private function makeAMQPChannelMock(bool $exceptional = true, array $times): MockInterface
    {
        $AMQPQueueMock = $this->makeAMQPQueueMock($exceptional, $times);
        $AMQPChannelMock = Mockery::mock(AMQPChannel::class);
        $AMQPChannelMock
            ->shouldReceive('getConsumers')
            ->times($times['getConsumers'])
            ->andReturn([$AMQPQueueMock]);
        $AMQPChannelMock
            ->shouldReceive('isConnected')
            ->times($times['isConnected'])
            ->andReturn(true);

        return $AMQPChannelMock;
    }

    /**
     * Return AMQPQueue mock object.
     *
     * @param bool    $exceptional
     * @param mixed[] $times
     *
     * @return MockInterface
     */
    private function makeAMQPQueueMock(bool $exceptional, array $times): MockInterface
    {
        $AMQPQueueMock = Mockery::mock(AMQPQueue::class);

        if (true === $exceptional) {
            $AMQPQueueMock
                ->shouldReceive('consume')
                ->times($times['consume'])
                ->andThrow(AMQPConnectionException::class)
                ->shouldReceive('consume')
                ->zeroOrMoreTimes()
                ->andReturn(true);
        } else {
            $AMQPQueueMock
                ->shouldReceive('consume')
                ->times($times['consume'])
                ->andReturn('');
        }

        $AMQPQueueMock
            ->shouldReceive('cancel')
            ->times($times['cancel'])
            ->andReturn('');

        return $AMQPQueueMock;
    }

    /**
     * Return AmqpSubscriptionConsumer mock object.
     *
     * @param mixed[] $times
     *
     * @return MockInterface
     */
    private function makeAmqpSubscriptionConsumerMock(array $times): MockInterface
    {
        $amqpSubscriptionConsumerMock = Mockery::mock(AmqpSubscriptionConsumer::class);
        $amqpSubscriptionConsumerMock
            ->shouldReceive('subscribe')
            ->times($times['subscribe']);

        $amqpSubscriptionConsumerMock->subscribers = [];

        return $amqpSubscriptionConsumerMock;
    }

    /**
     * Return Logger mock object.
     *
     * @param mixed[] $times
     *
     * @return MockInterface
     */
    protected function makeLoggerMock(array $times): MockInterface
    {
        $loggerMock = Mockery::mock(LoggerInterface::class);
        $loggerMock
            ->shouldReceive('warning')
            ->times($times['warning'])
            ->andReturn('');

        return $loggerMock;
    }
}
