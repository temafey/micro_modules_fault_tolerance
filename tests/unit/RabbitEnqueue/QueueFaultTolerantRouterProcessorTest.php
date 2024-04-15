<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\RabbitEnqueue;

use AdgoalCommon\FaultTolerance\RabbitEnqueue\Exception\QueueFaultTolerantRouterProcessorException;
use AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantRouterProcessor;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\CircuitBreaker\CircuitBreakerInterfaceMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Interop\QueueMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Psr\LogMockHelper;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Test for class QueueFaultTolerantRouterProcessor.
 *
 * @class QueueFaultTolerantRouterProcessorTest
 */
class QueueFaultTolerantRouterProcessorTest extends TestCase
{
    use QueueMockHelper, CircuitBreakerInterfaceMockHelper, LogMockHelper, MockeryPHPUnitIntegration;

    /**
     * Test for "The method has to return either self::ACK, self::REJECT, self::REQUEUE string".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantRouterProcessor::process
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantRouterProcessorDataProvider::getDataForProcessMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function processToRouterProcessorShouldReturnStringTest(array $mockArgs, array $mockTimes): void
    {
        $interopQueueProcessorMock = $this->createInteropQueueProcessorMock($mockArgs['Processor'], $mockTimes['Processor']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantRouterProcessor($interopQueueProcessorMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $interopQueueMessageMock = $this->createInteropQueueMessageMock($mockArgs['Message'], $mockTimes['Message']);
        $interopQueueContextMock = $this->createInteropQueueContextMock($mockArgs['Context'], $mockTimes['Context']);
        $result = $test->process($interopQueueMessageMock, $interopQueueContextMock);

        self::assertEquals($mockArgs['process'], $result);
    }

    /**
     * Test for "The method has to return either self::ACK, self::REJECT, self::REQUEUE string".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantRouterProcessor::process
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantRouterProcessorDataProvider::getDataForSendEventIsAvailableFalseMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function processToRouterProcessorWithNotAvailableAndNotBlockedServiceTest(array $mockArgs, array $mockTimes): void
    {
        $interopQueueProcessorMock = $this->createInteropQueueProcessorMock($mockArgs['Processor'], $mockTimes['Processor']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantRouterProcessor($interopQueueProcessorMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $interopQueueMessageMock = $this->createInteropQueueMessageMock($mockArgs['Message'], $mockTimes['Message']);
        $interopQueueContextMock = $this->createInteropQueueContextMock($mockArgs['Context'], $mockTimes['Context']);
        $result = $test->process($interopQueueMessageMock, $interopQueueContextMock);

        self::assertEquals($mockArgs['process'], $result);
    }

    /**
     * Test for "The method has to return either self::ACK, self::REJECT, self::REQUEUE string".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantRouterProcessor::process
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantRouterProcessorDataProvider::getDataForSendEventIsAvailableFalseIsBlockedTrueMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function processToRouterProcessorWithNotAvailableAndBlockedServiceShouldThrowMessageWasNotSentToQueueExceptionTest(array $mockArgs, array $mockTimes): void
    {
        $this->expectException(QueueFaultTolerantRouterProcessorException::class);
        $this->expectExceptionMessage('Service has been blocked.');

        $interopQueueProcessorMock = $this->createInteropQueueProcessorMock($mockArgs['Processor'], $mockTimes['Processor']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantRouterProcessor($interopQueueProcessorMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $interopQueueMessageMock = $this->createInteropQueueMessageMock($mockArgs['Message'], $mockTimes['Message']);
        $interopQueueContextMock = $this->createInteropQueueContextMock($mockArgs['Context'], $mockTimes['Context']);
        $result = $test->process($interopQueueMessageMock, $interopQueueContextMock);

        self::assertEquals($mockArgs['process'], $result);
    }

    /**
     * Test for "The method has to return either self::ACK, self::REJECT, self::REQUEUE string".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantRouterProcessor::process
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantRouterProcessorDataProvider::getDataForSendEventIsAvailableFalseIsBlockedTrueWithExceptionMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function processToRouterProcessorWithNotAvailableAndBlockedServiceShouldThrowQueueFaultTolerantRouterProcessorExceptionTest(array $mockArgs, array $mockTimes): void
    {
        $this->expectException(QueueFaultTolerantRouterProcessorException::class);

        $interopQueueProcessorMock = $this->createInteropQueueProcessorMock($mockArgs['Processor'], $mockTimes['Processor']);
        $mockArgs['DeepCopy']['copy'] = $interopQueueProcessorMock;
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantRouterProcessor($interopQueueProcessorMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $loggerMock = $this->createPsrLogLoggerInterfaceMock($mockTimes['LoggerInterface']);
        $test->setLogger($loggerMock);
        $interopQueueMessageMock = $this->createInteropQueueMessageMock($mockArgs['Message'], $mockTimes['Message']);
        $interopQueueContextMock = $this->createInteropQueueContextMock($mockArgs['Context'], $mockTimes['Context']);
        $test->process($interopQueueMessageMock, $interopQueueContextMock);
    }
}
