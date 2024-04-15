<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\RabbitEnqueue;

use AdgoalCommon\FaultTolerance\RabbitEnqueue\Exception\MessageWasNotSentToQueueException;
use AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\CircuitBreaker\CircuitBreakerInterfaceMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\DeepCopy\DeepCopyMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Enqueue\ClientMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Enqueue\RpcMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Interop\QueueMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Psr\LogMockHelper;
use Enqueue\Client\TraceableProducer;
use Enqueue\Rpc\Promise;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Test for class QueueFaultTolerantProducer.
 *
 * @class QueueFaultTolerantProducerTest
 */
class QueueFaultTolerantProducerTest extends TestCase
{
    use QueueMockHelper, RpcMockHelper, ClientMockHelper, CircuitBreakerInterfaceMockHelper, DeepCopyMockHelper, LogMockHelper, MockeryPHPUnitIntegration;

    /**
     * Test for "The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendEvent".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantProducerDataProvider::getDataForSendEventMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws MessageWasNotSentToQueueException
     */
    public function sendEventToProducerTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueClientProducerInterfaceMock = $this->createEnqueueClientProducerInterfaceMock($mockArgs['ProducerInterface'], $mockTimes['ProducerInterface']);
        $traceableProducer = new TraceableProducer($enqueueClientProducerInterfaceMock);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $deepCopyDeepCopyMock = $this->createDeepCopyDeepCopyMock($mockArgs['DeepCopy'], $mockTimes['DeepCopy']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantProducer($traceableProducer, $circuitBreakerCircuitBreakerInterfaceMock, $deepCopyDeepCopyMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $topic = $mockArgs['topic'];
        $message = $mockArgs['message'];
        $test->sendEvent($topic, $message);
        $traces = $traceableProducer->getTopicTraces($topic);

        self::assertCount(1, $traces);
        self::assertEquals($mockArgs['topic'], $traces[0]['topic']);
        self::assertEquals($mockArgs['message'], $traces[0]['body']);
    }

    /**
     * Test for "The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendEvent".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantProducerDataProvider::getDataForSendEventIsAvailableFalseMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws MessageWasNotSentToQueueException
     */
    public function sendEventToProducerWithNotAvailableAndNotBlockedServiceTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueClientProducerInterfaceMock = $this->createEnqueueClientProducerInterfaceMock($mockArgs['ProducerInterface'], $mockTimes['ProducerInterface']);
        $traceableProducer = new TraceableProducer($enqueueClientProducerInterfaceMock);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $deepCopyDeepCopyMock = $this->createDeepCopyDeepCopyMock($mockArgs['DeepCopy'], $mockTimes['DeepCopy']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantProducer($traceableProducer, $circuitBreakerCircuitBreakerInterfaceMock, $deepCopyDeepCopyMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $topic = $mockArgs['topic'];
        $message = $mockArgs['message'];
        $test->sendEvent($topic, $message);
        $traces = $traceableProducer->getTopicTraces($topic);

        self::assertCount(1, $traces);
        self::assertEquals($mockArgs['topic'], $traces[0]['topic']);
        self::assertEquals($mockArgs['message'], $traces[0]['body']);
    }

    /**
     * Test for "The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendEvent".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantProducerDataProvider::getDataForSendEventIsAvailableFalseIsBlockedTrueMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function notSendEventToProducerWithNotAvailableAndBlockedServiceShouldThrowMessageWasNotSentToQueueExceptionTest(array $mockArgs, array $mockTimes): void
    {
        $this->expectException(MessageWasNotSentToQueueException::class);
        $this->expectExceptionMessage('Service has been blocked.');

        $enqueueClientProducerInterfaceMock = $this->createEnqueueClientProducerInterfaceMock($mockArgs['ProducerInterface'], $mockTimes['ProducerInterface']);
        $traceableProducer = new TraceableProducer($enqueueClientProducerInterfaceMock);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $deepCopyDeepCopyMock = $this->createDeepCopyDeepCopyMock($mockArgs['DeepCopy'], $mockTimes['DeepCopy']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantProducer($traceableProducer, $circuitBreakerCircuitBreakerInterfaceMock, $deepCopyDeepCopyMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $topic = $mockArgs['topic'];
        $message = $mockArgs['message'];
        $test->sendEvent($topic, $message);
    }

    /**
     * Test for "The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendEvent".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantProducerDataProvider::getDataForSendEventIsAvailableFalseIsBlockedTrueWithExceptionMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function sendEventToProducerThrowExceptionWithNotAvailableAndBlockedServiceShouldThrowMessageWasNotSentToQueueExceptionTest(array $mockArgs, array $mockTimes): void
    {
        $this->expectException(MessageWasNotSentToQueueException::class);

        $enqueueClientProducerInterfaceMock = $this->createEnqueueClientProducerInterfaceMock($mockArgs['ProducerInterface'], $mockTimes['ProducerInterface']);
        $traceableProducer = new TraceableProducer($enqueueClientProducerInterfaceMock);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $mockArgs['DeepCopy']['copy'] = $traceableProducer;
        $deepCopyDeepCopyMock = $this->createDeepCopyDeepCopyMock($mockArgs['DeepCopy'], $mockTimes['DeepCopy']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantProducer($traceableProducer, $circuitBreakerCircuitBreakerInterfaceMock, $deepCopyDeepCopyMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $topic = $mockArgs['topic'];
        $message = $mockArgs['message'];
        $test->sendEvent($topic, $message);
    }

    /**
     * Test for "The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendCommand".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer::sendCommand
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantProducerDataProvider::getDataForSendCommandMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws MessageWasNotSentToQueueException
     * @throws \AdgoalCommon\Base\Domain\Exception\LoggerException
     */
    public function sendCommandToProducerTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueClientProducerInterfaceMock = $this->createEnqueueClientProducerInterfaceMock($mockArgs['ProducerInterface'], $mockTimes['ProducerInterface']);
        $traceableProducer = new TraceableProducer($enqueueClientProducerInterfaceMock);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $deepCopyDeepCopyMock = $this->createDeepCopyDeepCopyMock($mockArgs['DeepCopy'], $mockTimes['DeepCopy']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantProducer($traceableProducer, $circuitBreakerCircuitBreakerInterfaceMock, $deepCopyDeepCopyMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $command = $mockArgs['command'];
        $message = $mockArgs['message'];
        $result = $test->sendCommand($command, $message);
        $traces = $traceableProducer->getCommandTraces($command);

        self::assertInstanceOf(Promise::class, $result);
        self::assertCount(1, $traces);
        self::assertEquals($mockArgs['command'], $traces[0]['command']);
        self::assertEquals($mockArgs['message'], $traces[0]['body']);
    }

    /**
     * Test for "The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendEvent".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantProducerDataProvider::getDataForSendCommandIsAvailableFalseIsBlockedFalseMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws MessageWasNotSentToQueueException
     * @throws \AdgoalCommon\Base\Domain\Exception\LoggerException
     */
    public function sendCommandToProducerWithNotAvailableAndNotBlockedServiceTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueClientProducerInterfaceMock = $this->createEnqueueClientProducerInterfaceMock($mockArgs['ProducerInterface'], $mockTimes['ProducerInterface']);
        $traceableProducer = new TraceableProducer($enqueueClientProducerInterfaceMock);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $deepCopyDeepCopyMock = $this->createDeepCopyDeepCopyMock($mockArgs['DeepCopy'], $mockTimes['DeepCopy']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantProducer($traceableProducer, $circuitBreakerCircuitBreakerInterfaceMock, $deepCopyDeepCopyMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $command = $mockArgs['command'];
        $message = $mockArgs['message'];
        $result = $test->sendCommand($command, $message);
        $traces = $traceableProducer->getCommandTraces($command);

        self::assertInstanceOf(Promise::class, $result);
        self::assertCount(1, $traces);
        self::assertEquals($mockArgs['command'], $traces[0]['command']);
        self::assertEquals($mockArgs['message'], $traces[0]['body']);
    }

    /**
     * Test for "The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendEvent".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantProducerDataProvider::getDataForSendCommandIsAvailableFalseIsBlockedTrueMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws MessageWasNotSentToQueueException
     * @throws \AdgoalCommon\Base\Domain\Exception\LoggerException
     */
    public function sendCommandWithNotAvailableAndBlockedServiceShouldThrowMessageWasNotSentToQueueExceptionTest(array $mockArgs, array $mockTimes): void
    {
        $this->expectException(MessageWasNotSentToQueueException::class);
        $this->expectExceptionMessage('Service has been blocked.');

        $enqueueClientProducerInterfaceMock = $this->createEnqueueClientProducerInterfaceMock($mockArgs['ProducerInterface'], $mockTimes['ProducerInterface']);
        $traceableProducer = new TraceableProducer($enqueueClientProducerInterfaceMock);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $deepCopyDeepCopyMock = $this->createDeepCopyDeepCopyMock($mockArgs['DeepCopy'], $mockTimes['DeepCopy']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantProducer($traceableProducer, $circuitBreakerCircuitBreakerInterfaceMock, $deepCopyDeepCopyMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $command = $mockArgs['command'];
        $message = $mockArgs['message'];
        $test->sendCommand($command, $message);
    }

    /**
     * Test for "The message could be pretty much everything as long as you have a client extension that transforms a body to string on onPreSendEvent".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantProducer::sendEvent
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantProducerDataProvider::getDataForSendCommandIsAvailableFalseIsBlockedTrueWithExceptionMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws MessageWasNotSentToQueueException
     * @throws \AdgoalCommon\Base\Domain\Exception\LoggerException
     */
    public function sendCommandToProducerThrowExceptionWithNotAvailableAndBlockedServiceShouldThrowMessageWasNotSentToQueueExceptionTest(array $mockArgs, array $mockTimes): void
    {
        $this->expectException(MessageWasNotSentToQueueException::class);

        $enqueueClientProducerInterfaceMock = $this->createEnqueueClientProducerInterfaceMock($mockArgs['ProducerInterface'], $mockTimes['ProducerInterface']);
        $traceableProducer = new TraceableProducer($enqueueClientProducerInterfaceMock);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $mockArgs['DeepCopy']['copy'] = $traceableProducer;
        $deepCopyDeepCopyMock = $this->createDeepCopyDeepCopyMock($mockArgs['DeepCopy'], $mockTimes['DeepCopy']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantProducer($traceableProducer, $circuitBreakerCircuitBreakerInterfaceMock, $deepCopyDeepCopyMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $command = $mockArgs['command'];
        $message = $mockArgs['message'];
        $test->sendCommand($command, $message);
    }
}
