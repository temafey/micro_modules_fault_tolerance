<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Tests\Unit\RabbitEnqueue;

use MicroModule\FaultTolerance\RabbitEnqueue\Exception\MessageWasNotSentToQueueException;
use MicroModule\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer;
use MicroModule\FaultTolerance\Tests\Unit\RepositoryTestCase;
use AMQPConnectionException;
use DeepCopy\DeepCopy;
use Enqueue\Client\ProducerInterface;
use Enqueue\Client\TraceableProducer;
use Interop\Queue\Exception\Exception as InteropQueueException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;

/**
 * Class QueueFaultTolerantProducerTest.
 *
 * @category Tests\Unit\Infrastructure\Service
 */
class QueueFaultTolerantProducerTest extends RepositoryTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     *
     * @group unit
     *
     * @covers      \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantProducer::sendEvent
     * @covers      \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantProducer::runFaultTolerantProcess
     *
     * @dataProvider \MicroModule\FaultTolerance\Tests\Unit\DataProvider\QueueFaultTolerantProducerProvider::getData()
     *
     * @param string $topic         Afm process unique id
     * @param string $item          Afm program row item
     * @param int    $retryAttempts
     * @param int    $retryTimeout
     *
     * @throws MessageWasNotSentToQueueException
     */
    public function sendEventToProducerTest(string $topic, string $item, int $retryAttempts, int $retryTimeout): void
    {
        $producerMock = $this->makeProducerMock();
        $traceableProducer = new TraceableProducer($producerMock);
        $this->assertInstanceOf(ProducerInterface::class, $traceableProducer);
        $deepClonerMock = $this->makeDeepClonerMock($traceableProducer, 1);

        $queueFaultTolerantProducer = new QueueFaultTolerantProducer(
            $traceableProducer,
            $deepClonerMock,
            $retryAttempts,
            $retryTimeout
        );
        $queueFaultTolerantProducer->sendEvent($topic, $item);
        $traces = $traceableProducer->getTopicTraces($topic);

        self::assertCount(1, $traces);
        self::assertEquals($topic, $traces[0]['topic']);
        self::assertEquals($item, $traces[0]['body']);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \MicroModule\FaultTolerance\Tests\Unit\DataProvider\QueueFaultTolerantProducerProvider::getData()
     *
     * @param string $topic         Afm process unique id
     * @param string $item          Afm program row item
     * @param int    $retryAttempts
     * @param int    $retryTimeout
     *
     * @throws MessageWasNotSentToQueueException
     */
    public function throwMessageWasNotSentExceptionAfterAMQPConnectionExceptionTest(string $topic, string $item, int $retryAttempts, int $retryTimeout): void
    {
        $this->expectException(MessageWasNotSentToQueueException::class);

        /** @var TraceableProducer $producer */
        $producer = $this->makeProducerMockWithThrowingException(AMQPConnectionException::class, $retryAttempts);
        $deepClonerMock = $this->makeDeepClonerMock($producer, $retryAttempts + 1);
        $loggerMock = $this->makeLoggerMock($retryAttempts);

        $queueFaultTolerantProducer = new QueueFaultTolerantProducer(
            $producer,
            $deepClonerMock,
            $retryAttempts,
            $retryTimeout
        );
        $queueFaultTolerantProducer->setLogger($loggerMock);
        $queueFaultTolerantProducer->sendEvent($topic, $item);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \MicroModule\FaultTolerance\Tests\Unit\DataProvider\QueueFaultTolerantProducerProvider::getData()
     *
     * @param string $topic         Afm process unique id
     * @param string $item          Afm program row item
     * @param int    $retryAttempts
     * @param int    $retryTimeout
     *
     * @throws MessageWasNotSentToQueueException
     */
    public function throwMessageWasNotSentExceptionAfterInteropQueueExceptionTest(
        string $topic,
        string $item,
        int $retryAttempts,
        int $retryTimeout
    ): void {
        $this->expectException(MessageWasNotSentToQueueException::class);

        /** @var TraceableProducer $producer */
        $producer = $this->makeProducerMockWithThrowingException(InteropQueueException::class, $retryAttempts);
        $deepClonerMock = $this->makeDeepClonerMock($producer, $retryAttempts + 1);
        $loggerMock = $this->makeLoggerMock($retryAttempts);

        $queueFaultTolerantProducer = new QueueFaultTolerantProducer(
            $producer,
            $deepClonerMock,
            $retryAttempts,
            $retryTimeout
        );
        $queueFaultTolerantProducer->setLogger($loggerMock);
        $queueFaultTolerantProducer->sendEvent($topic, $item);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \MicroModule\FaultTolerance\RedisAlerting\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \MicroModule\FaultTolerance\Tests\Unit\DataProvider\QueueFaultTolerantProducerProvider::getData()
     *
     * @param string $topic         Afm process unique id
     * @param string $item          Afm program row item
     * @param int    $retryAttempts
     * @param int    $retryTimeout
     *
     * @throws MessageWasNotSentToQueueException
     */
    public function addDataToProducerAfterExceptionAndRetryTest(
        string $topic,
        string $item,
        int $retryAttempts,
        int $retryTimeout
    ): void {
        /** @var TraceableProducer $producer */
        $producer = $this->makeProducerMockWithThrowingException(InteropQueueException::class, $retryAttempts - 1);
        $deepClonerMock = $this->makeDeepClonerMock($producer, $retryAttempts);
        $loggerMock = $this->makeLoggerMock($retryAttempts - 1);

        $queueFaultTolerantProducer = new QueueFaultTolerantProducer($producer, $deepClonerMock, $retryAttempts, $retryTimeout);
        $queueFaultTolerantProducer->setLogger($loggerMock);
        $queueFaultTolerantProducer->sendEvent($topic, $item);
    }

    /**
     * Return ProducerInterface mock object.
     *
     * @return MockInterface
     */
    protected function makeProducerMock(): MockInterface
    {
        $mock = Mockery::mock(ProducerInterface::class);
        $mock
            ->shouldReceive('sendEvent')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        return $mock;
    }

    /**
     * Return ProducerInterface mock object.
     *
     * @param string $exceptionClass
     * @param int    $retryAttemps
     *
     * @return MockInterface
     */
    protected function makeProducerMockWithThrowingException(string $exceptionClass, int $retryAttemps): MockInterface
    {
        $mock = Mockery::mock(ProducerInterface::class);
        $mock
            ->shouldReceive('sendEvent')
            ->times($retryAttemps)
            ->andThrow($exceptionClass)
            ->shouldReceive('sendEvent')
            ->zeroOrMoreTimes()
            ->andReturn(true);

        return $mock;
    }

    /**
     * Return DeepCopy mock object.
     *
     * @param ProducerInterface $producer
     * @param int               $times
     *
     * @return MockInterface
     */
    protected function makeDeepClonerMock(ProducerInterface $producer, int $times = 0): MockInterface
    {
        $mock = Mockery::mock(DeepCopy::class);
        $mock
            ->shouldReceive('copy')
            ->times($times)
            ->andReturn($producer);

        return $mock;
    }

    /**
     * Return Logger mock object.
     *
     * @param int $retryAttempts
     *
     * @return MockInterface
     */
    protected function makeLoggerMock(int $retryAttempts): MockInterface
    {
        $loggerMock = Mockery::mock(LoggerInterface::class);
        $loggerMock
            ->shouldReceive('warning')
            ->times($retryAttempts)
            ->andReturn('');

        return $loggerMock;
    }
}
