<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\RabbitEnqueue;

use AdgoalCommon\FaultTolerance\RabbitEnqueue\Exception\QueueFaultTolerantConsumerException;
use AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\CircuitBreaker\CircuitBreakerInterfaceMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Enqueue\ConsumptionMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Interop\QueueMockHelper;
use AdgoalCommon\FaultTolerance\Tests\Unit\Mock\Vendor\Psr\LogMockHelper;
use Enqueue\Consumption\QueueConsumerInterface;
use Interop\Queue\Context;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Test for class QueueFaultTolerantConsumer.
 *
 * @class QueueFaultTolerantConsumerTest
 */
class QueueFaultTolerantConsumerTest extends TestCase
{
    use QueueMockHelper, ConsumptionMockHelper, CircuitBreakerInterfaceMockHelper, LogMockHelper, MockeryPHPUnitIntegration;

    /**
     * Test for "Set receive timeout".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::setReceiveTimeout
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForSetReceiveTimeoutMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function setReceiveTimeoutShouldCallTheSameMethodInQueueConsumerTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $timeout = $mockArgs['timeout'];
        $test->setReceiveTimeout($timeout);
    }

    /**
     * Test for "In milliseconds".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::getReceiveTimeout
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForGetReceiveTimeoutMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function getReceiveTimeoutShouldCallTheSameMethodInQueueConsumerAndReturnIntTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $result = $test->getReceiveTimeout();

        self::assertEquals($mockArgs['getReceiveTimeout'], $result);
    }

    /**
     * Test for "Return Queue Context object".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::getContext
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForGetContextMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function getContextShouldCallTheSameMethodInQueueConsumerAndReturnContextTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $result = $test->getContext();

        self::assertInstanceOf(Context::class, $result);
    }

    /**
     * Test for "Bind enqueue processor by queue name".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::bind
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForBindMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function bindShouldCallTheSameMethodInQueueConsumerAndReturnQueueConsumerInterfaceTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $queueName = $mockArgs['queueName'];
        $interopQueueProcessorMock = $this->createInteropQueueProcessorMock($mockArgs['Processor'], $mockTimes['Processor']);
        $result = $test->bind($queueName, $interopQueueProcessorMock);

        self::assertInstanceOf(QueueConsumerInterface::class, $result);
    }

    /**
     * Test for "Bind enqueue callback by queue name".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::bindCallback
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForBindCallbackMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     */
    public function bindCallbackShouldCallTheSameMethodInQueueConsumerAndReturnQueueConsumerInterfaceTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $queueName = $mockArgs['queueName'];
        $processor = $mockArgs['processor'];
        $result = $test->bindCallback($queueName, $processor);

        self::assertInstanceOf(QueueConsumerInterface::class, $result);
    }

    /**
     * Test for "Runtime extension - is an extension or a collection of extensions which could be set on runtime".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::consume
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForConsumeMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws \Exception
     */
    public function consumeShouldCallTheSameMethodInQueueConsumerTest(array $mockArgs, array $mockTimes): void
    {
        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $enqueueConsumptionExtensionInterfaceMock = $this->createEnqueueConsumptionExtensionInterfaceMock($mockArgs['ExtensionInterface'], $mockTimes['ExtensionInterface']);
        $test->consume($enqueueConsumptionExtensionInterfaceMock);
    }

    /**
     * Test for "Runtime extension - is an extension or a collection of extensions which could be set on runtime".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::consume
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForConsumeMethodIsAvailableFalseMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws \Exception
     */
    public function consumeShouldCallTheSameMethodInQueueConsumerWithNotAvailableAndNotBlockedServiceTes(array $mockArgs, array $mockTimes): void
    {
        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $enqueueConsumptionExtensionInterfaceMock = $this->createEnqueueConsumptionExtensionInterfaceMock($mockArgs['ExtensionInterface'], $mockTimes['ExtensionInterface']);
        $test->consume($enqueueConsumptionExtensionInterfaceMock);
    }

    /**
     * Test for "Runtime extension - is an extension or a collection of extensions which could be set on runtime".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::consume
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForConsumeMethodIsAvailableFalseIsBlockedTrueMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws \Exception
     */
    public function consumeShouldCallTheSameMethodInQueueConsumerWithNotAvailableAndBlockedServiceShouldThrowMessageWasNotSentToQueueExceptionTest(array $mockArgs, array $mockTimes): void
    {
        $this->expectException(QueueFaultTolerantConsumerException::class);
        $this->expectExceptionMessage('Service has been blocked.');

        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $enqueueConsumptionExtensionInterfaceMock = $this->createEnqueueConsumptionExtensionInterfaceMock($mockArgs['ExtensionInterface'], $mockTimes['ExtensionInterface']);
        $test->consume($enqueueConsumptionExtensionInterfaceMock);
    }

    /**
     * Test for "Runtime extension - is an extension or a collection of extensions which could be set on runtime".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RabbitEnqueue\QueueFaultTolerantConsumer::consume
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\RabbitEnqueue\QueueFaultTolerantConsumerDataProvider::getDataForConsumeMethodIsAvailableFalseIsBlockedTrueWithExceptionMethod()
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @throws \Exception
     */
    public function consumeShouldCallTheSameMethodInQueueConsumerThrowExceptionWithNotAvailableAndBlockedServiceShouldThrowQueueFaultTolerantConsumerExceptionTest(array $mockArgs, array $mockTimes): void
    {
        $this->expectException(QueueFaultTolerantConsumerException::class);

        $enqueueConsumptionQueueConsumerInterfaceMock = $this->createEnqueueConsumptionQueueConsumerInterfaceMock($mockArgs['QueueConsumerInterface'], $mockTimes['QueueConsumerInterface']);
        $enqueueConsumptionQueueConsumerInterfaceMock->interopContext = $enqueueConsumptionQueueConsumerInterfaceMock->getContext();
        $circuitBreakerCircuitBreakerInterfaceMock = $this->createCircuitBreakerCircuitBreakerInterfaceMock($mockArgs['CircuitBreakerInterface'], $mockTimes['CircuitBreakerInterface']);
        $retryTimeout = 10;
        $test = new QueueFaultTolerantConsumer($enqueueConsumptionQueueConsumerInterfaceMock, $circuitBreakerCircuitBreakerInterfaceMock, $retryTimeout);
        $enqueueConsumptionExtensionInterfaceMock = $this->createEnqueueConsumptionExtensionInterfaceMock($mockArgs['ExtensionInterface'], $mockTimes['ExtensionInterface']);
        $test->consume($enqueueConsumptionExtensionInterfaceMock);
    }
}
