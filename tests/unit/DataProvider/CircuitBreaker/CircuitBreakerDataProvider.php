<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\CircuitBreaker;

/**
 * DataProvider for class {testClassName}.
 */
class CircuitBreakerDataProvider
{
    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForIsAvailableMethod(): array
    {
        return [
            0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'getMaxFailures' => 3,
                    'serviceName' => 'test',
                ],
            ],
        ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataDifferentServicesMethod(): array
    {
        return [
            0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10000000,
                    'getMaxFailures' => 3,
                    'serviceName1' => 'test',
                    'serviceName2' => 'test1',
                    'serviceName3' => 'test2',
                ],
            ],
        ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForIsAvailableLongTimeoutMethod(): array
    {
        return [
              0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 1000000,
                    'getMaxFailures' => 3,
                    'serviceName' => 'test',
                ],
              ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForIsAvailableShortTimeoutMethod(): array
    {
        return [
            0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'getMaxFailures' => 3,
                    'serviceName' => 'test',
                ],
            ],
        ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForIsBlockedMethod(): array
    {
        return [
              0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'getMaxFailures' => 3,
                    'serviceName' => 'test',
                ],
              ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForReportFailureMethod(): array
    {
        return [
              0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'getMaxFailures' => 3,
                    'serviceName' => 'test',
                ],
              ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForReportSuccessMethod(): array
    {
        return [
              0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'getMaxFailures' => 3,
                    'serviceName' => 'test',
                ],
              ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForGetMaxFailuresMethod(): array
    {
        return [
              0 => [
                0 => [
                  'maxFailures' => 3,
                  'criticalFailures' => 6,
                  'retryTimeout' => 10,
                  'getMaxFailures' => 3,
                  'serviceName' => 'test',
                ],
              ],
            1 => [
                0 => [
                    'maxFailures' => 2,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'getMaxFailures' => 2,
                    'serviceName' => 'test',
                ],
            ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForGetCriticalFailuresMethod(): array
    {
        return [
              0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'getCriticalFailures' => 6,
                    'serviceName' => 'test',
                ],
              ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForGetRetryTimeoutMethod(): array
    {
        return [
              0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'getRetryTimeout' => 10,
                    'serviceName' => 'test',
                ],
              ],
            ];
    }

    /**
     * Return test data for AdgoalCommon\FaultTolerance\CircuitBreaker\CircuitBreaker.
     *
     * @return mixed[]
     */
    public function getDataForGetLastTestMethod(): array
    {
        return [
              0 => [
                0 => [
                    'maxFailures' => 3,
                    'criticalFailures' => 6,
                    'retryTimeout' => 10,
                    'serviceName' => 'test',
                ],
              ],
            ];
    }
}
