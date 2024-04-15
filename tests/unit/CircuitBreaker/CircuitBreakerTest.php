<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\CircuitBreaker;

use AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker;
use PHPUnit\Framework\TestCase;

/**
 * Test for class CircuitBreaker.
 *
 * @class CircuitBreakerTest
 */
class CircuitBreakerTest extends TestCase
{
    /**
     * Test for "Check if service is available (according to CB knowledge)".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::isAvailable
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForIsAvailableMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function isAvailableShouldReturnTrueIfServiceFailuresLessThenMaxFailuresTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];
        $result = $test->isAvailable($serviceName);

        self::assertTrue($result);
    }

    /**
     * Test for "Check if service is available (according to CB knowledge)".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::isAvailable
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForIsAvailableLongTimeoutMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function isAvailableShouldReturnFalseIfServiceFailuresEqualOrMoreThenMaxFailuresDuringTimeoutTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];

        for ($i = 0; $i < $mockArgs['maxFailures']; ++$i) {
            $test->reportFailure($serviceName);
        }
        $result = $test->isAvailable($serviceName);
        self::assertFalse($result);

        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];

        for ($i = 0; $i <= $mockArgs['maxFailures']; ++$i) {
            $test->reportFailure($serviceName);
        }
        $test->reportFailure($serviceName);
        $result = $test->isAvailable($serviceName);
        self::assertFalse($result);
    }

    /**
     * Test for "Check if service is available (according to CB knowledge)".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::isAvailable
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForIsAvailableShortTimeoutMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function isAvailableShouldReturnTrueIfServiceFailuresEqualOrMoreThenMaxFailuresAfterTimeoutTest(array $mockArgs): void
    {
        $serviceName = $mockArgs['serviceName'];

        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        for ($i = 0; $i < $mockArgs['maxFailures']; ++$i) {
            $test->reportFailure($serviceName);
        }
        $result = $test->isAvailable($serviceName);
        self::assertTrue($result);

        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];

        for ($i = 0; $i <= $mockArgs['maxFailures']; ++$i) {
            $test->reportFailure($serviceName);
        }
        $test->reportFailure($serviceName);
        $result = $test->isAvailable($serviceName);
        self::assertTrue($result);
    }

    /**
     * Test for "Check if service available (according to CB knowledge)".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::isBlocked
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForIsBlockedMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function isBlockedShouldReturnFalseIfServiceFailuresLessThenCriticalTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];
        $result = $test->isBlocked($serviceName);

        self::assertFalse($result);
    }

    /**
     * Test for "Check if service has been blocked (according to CB knowledge)".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::isBlocked
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForIsBlockedMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function isBlockedShouldReturnTrueIfServiceFailuresEqualOrMoreThenCriticalTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];

        for ($i = 0; $i < $mockArgs['criticalFailures']; ++$i) {
            $test->reportFailure($serviceName);
        }
        $result = $test->isBlocked($serviceName);

        self::assertTrue($result);

        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];

        for ($i = 0; $i <= $mockArgs['criticalFailures']; ++$i) {
            $test->reportFailure($serviceName);
        }
        $result = $test->isBlocked($serviceName);

        self::assertTrue($result);
    }

    /**
     * Test for "Use this method to let CB know that you failed to connect to the".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::reportFailure
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForReportFailureMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function reportFailureShouldIncrementServiceFailuresTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);

        $serviceName = $mockArgs['serviceName'];
        $test->reportFailure($serviceName);
        $failures = $test->getFailures($serviceName);
        self::assertEquals(1, $failures);

        $test->reportFailure($serviceName);
        $failures = $test->getFailures($serviceName);
        self::assertEquals(2, $failures);
    }

    /**
     * Test for "Use this method to let CB know that you successfully connected to the".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::reportSuccess
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForReportSuccessMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function reportSuccessShouldDecrementServiceFailuresTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);

        $serviceName = $mockArgs['serviceName'];
        $failures = $test->getFailures($serviceName);
        self::assertEquals(0, $failures);

        $test->reportSuccess($serviceName);
        $failures = $test->getFailures($serviceName);
        self::assertEquals(0, $failures);

        $test->reportFailure($serviceName);
        $failures = $test->getFailures($serviceName);
        self::assertEquals(1, $failures);
        $test->reportSuccess($serviceName);
        $failures = $test->getFailures($serviceName);
        self::assertEquals(0, $failures);

        $test->reportFailure($serviceName);
        $test->reportFailure($serviceName);
        $test->reportFailure($serviceName);
        $failures = $test->getFailures($serviceName);
        self::assertEquals(3, $failures);
        $test->reportSuccess($serviceName);
        $failures = $test->getFailures($serviceName);
        self::assertEquals(2, $failures);
    }

    /**
     * Test for "Return max count of failed request".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::getMaxFailures
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForGetMaxFailuresMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function getMaxFailuresShouldReturnIntTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];
        $result = $test->getMaxFailures($serviceName);

        self::assertEquals($mockArgs['getMaxFailures'], $result);
    }

    /**
     * Test for "Return crititak count of failed request".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::getCriticalFailures
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForGetCriticalFailuresMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function getCriticalFailuresShouldReturnIntTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];
        $result = $test->getCriticalFailures($serviceName);

        self::assertEquals($mockArgs['getCriticalFailures'], $result);
    }

    /**
     * Test for "Return retry timeout after failed request".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::getRetryTimeout
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForGetRetryTimeoutMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function getRetryTimeoutShouldReturnIntTest(array $mockArgs): void
    {
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];
        $result = $test->getRetryTimeout($serviceName);

        self::assertEquals($mockArgs['getRetryTimeout'], $result);
    }

    /**
     * Test for "Return last failed request".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::getLastTest
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataForGetLastTestMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function getLastTestShouldReturnIntTest(array $mockArgs): void
    {
        $time = hrtime(true);
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);
        $serviceName = $mockArgs['serviceName'];

        $test->reportFailure($serviceName);
        $result = $test->getLastTest($serviceName);
        self::assertGreaterThan($time, $result);

        $test->reportFailure($serviceName);
        $result2 = $test->getLastTest($serviceName);
        self::assertGreaterThan($result, $result2);
    }

    /**
     * Test for "Check if service is available (according to CB knowledge)".
     *
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker::isAvailable
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker\CircuitBreakerDataProvider::getDataDifferentServicesMethod()
     *
     * @param mixed[] $mockArgs
     */
    public function differentServicesShouldProcessDifferentlyTest(array $mockArgs): void
    {
        $serviceName1 = $mockArgs['serviceName1'];
        $serviceName2 = $mockArgs['serviceName2'];
        $serviceName3 = $mockArgs['serviceName3'];

        //All services available
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);

        $result = $test->isAvailable($serviceName1);
        self::assertTrue($result);

        $result = $test->isAvailable($serviceName2);
        self::assertTrue($result);

        $result = $test->isAvailable($serviceName3);
        self::assertTrue($result);

        //First service is not available
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);

        for ($i = 0; $i < $mockArgs['maxFailures']; ++$i) {
            $test->reportFailure($serviceName1);
        }
        $result = $test->isAvailable($serviceName1);
        self::assertFalse($result);

        $result = $test->isAvailable($serviceName2);
        self::assertTrue($result);

        $result = $test->isAvailable($serviceName3);
        self::assertTrue($result);

        //Second service has been blocked
        $test = new CircuitBreaker($mockArgs['maxFailures'], $mockArgs['criticalFailures'], $mockArgs['retryTimeout']);

        for ($i = 0; $i < $mockArgs['criticalFailures']; ++$i) {
            $test->reportFailure($serviceName2);
        }
        $result = $test->isBlocked($serviceName1);
        self::assertFalse($result);

        $result = $test->isBlocked($serviceName2);
        self::assertTrue($result);

        $result = $test->isBlocked($serviceName3);
        self::assertFalse($result);
    }
}
