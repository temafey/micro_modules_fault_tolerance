<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\Mock\CircuitBreaker;

use AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreakerInterface;
use Mockery;
use Mockery\MockInterface;

/**
 * Mock helper trait.
 */
trait CircuitBreakerInterfaceMockHelper
{
    /**
     * Create and return mock object for class AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreakerInterface.
     *
     * @param mixed[] $mockArgs
     * @param mixed[] $mockTimes
     *
     * @return MockInterface|CircuitBreakerInterface
     */
    protected function createCircuitBreakerCircuitBreakerInterfaceMock(
        array $mockArgs = ['isAvailable' => '', 'isBlocked' => '', 'reportFailure' => '', 'reportSuccess' => '', 'getMaxFailures' => '', 'getCriticalFailures' => '', 'getRetryTimeout' => '', 'getFailures' => '', 'getLastTest' => ''],
        array $mockTimes = ['isAvailable' => 0, 'isBlocked' => 0, 'reportFailure' => 0, 'reportSuccess' => 0, 'getMaxFailures' => 0, 'getCriticalFailures' => 0, 'getRetryTimeout' => 0, 'getFailures' => 0, 'getLastTest' => 0]
    ): MockInterface {
        $mock = Mockery::namedMock('Mock\AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreakerInterface', CircuitBreakerInterface::class);

        if (array_key_exists('isAvailable', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('isAvailable');

            if (null === $mockTimes['isAvailable']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['isAvailable'])) {
                $mockMethod->times($mockTimes['isAvailable']['times']);
            } else {
                $mockMethod->times($mockTimes['isAvailable']);
            }

            if (!is_array($mockArgs['isAvailable'])) {
                $mockArgs['isAvailable'] = [$mockArgs['isAvailable']];
            }
            $mockMethod->andReturn(...$mockArgs['isAvailable']);
        }

        if (array_key_exists('isBlocked', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('isBlocked');

            if (null === $mockTimes['isBlocked']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['isBlocked'])) {
                $mockMethod->times($mockTimes['isBlocked']['times']);
            } else {
                $mockMethod->times($mockTimes['isBlocked']);
            }

            if (!is_array($mockArgs['isBlocked'])) {
                $mockArgs['isBlocked'] = [$mockArgs['isBlocked']];
            }
            $mockMethod->andReturn(...$mockArgs['isBlocked']);
        }

        if (array_key_exists('reportFailure', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('reportFailure');

            if (null === $mockTimes['reportFailure']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['reportFailure'])) {
                $mockMethod->times($mockTimes['reportFailure']['times']);
            } else {
                $mockMethod->times($mockTimes['reportFailure']);
            }
        }

        if (array_key_exists('reportSuccess', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('reportSuccess');

            if (null === $mockTimes['reportSuccess']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['reportSuccess'])) {
                $mockMethod->times($mockTimes['reportSuccess']['times']);
            } else {
                $mockMethod->times($mockTimes['reportSuccess']);
            }
        }

        if (array_key_exists('getMaxFailures', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('getMaxFailures');

            if (null === $mockTimes['getMaxFailures']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['getMaxFailures'])) {
                $mockMethod->times($mockTimes['getMaxFailures']['times']);
            } else {
                $mockMethod->times($mockTimes['getMaxFailures']);
            }
            $mockMethod->andReturn($mockArgs['getMaxFailures']);
        }

        if (array_key_exists('getCriticalFailures', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('getCriticalFailures');

            if (null === $mockTimes['getCriticalFailures']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['getCriticalFailures'])) {
                $mockMethod->times($mockTimes['getCriticalFailures']['times']);
            } else {
                $mockMethod->times($mockTimes['getCriticalFailures']);
            }
            $mockMethod->andReturn($mockArgs['getCriticalFailures']);
        }

        if (array_key_exists('getRetryTimeout', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('getRetryTimeout');

            if (null === $mockTimes['getRetryTimeout']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['getRetryTimeout'])) {
                $mockMethod->times($mockTimes['getRetryTimeout']['times']);
            } else {
                $mockMethod->times($mockTimes['getRetryTimeout']);
            }
            $mockMethod->andReturn($mockArgs['getRetryTimeout']);
        }

        if (array_key_exists('getFailures', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('getFailures');

            if (null === $mockTimes['getFailures']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['getFailures'])) {
                $mockMethod->times($mockTimes['getFailures']['times']);
            } else {
                $mockMethod->times($mockTimes['getFailures']);
            }
            $mockMethod->andReturn($mockArgs['getFailures']);
        }

        if (array_key_exists('getLastTest', $mockTimes)) {
            $mockMethod = $mock
                ->shouldReceive('getLastTest');

            if (null === $mockTimes['getLastTest']) {
                $mockMethod->zeroOrMoreTimes();
            } elseif (is_array($mockTimes['getLastTest'])) {
                $mockMethod->times($mockTimes['getLastTest']['times']);
            } else {
                $mockMethod->times($mockTimes['getLastTest']);
            }
            $mockMethod->andReturn($mockArgs['getLastTest']);
        }

        return $mock;
    }
}
