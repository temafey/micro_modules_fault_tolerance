<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\CircuitBreaker;

/**
 * Allows user code to track avability/unavailability of any service by serviceName.
 *
 * Circuit breaker counts each failure and once you reach limit it will skip connection attempt with instant failure.
 * You can also set retry timeout per service. Then after retry timeout seconds CircuitBreaker will allow one
 * thread to try to connect to the service again.
 *      - If thread fails CircuitBreaker waits till next retry timeout.
 *      - If thread succeeds, more threads will be allowed to connect.
 */
interface CircuitBreakerInterface
{
    /**
     * Check if service is available (according to CB knowledge).
     *
     * @param string $serviceName - arbitrary service name
     *
     * @return bool true if service is available, false if service is down
     */
    public function isAvailable(string $serviceName): bool;

    /**
     * Check if service is blocked (according to CB knowledge).
     *
     * @param string $serviceName - arbitrary service name
     *
     * @return bool true if service is available, false if service is down
     */
    public function isBlocked(string $serviceName): bool;

    /**
     * Use this method to let CB know that you failed to connect to the
     * service of particular name.
     *
     * Allows CB to update its stats accordingly for future HTTP requests.
     *
     * @param string $serviceName - arbitrary service name
     */
    public function reportFailure(string $serviceName): void;

    /**
     * Use this method to let CB know that you successfully connected to the
     * service of particular name.
     *
     * Allows CB to update its stats accordingly for future HTTP requests.
     *
     * @param string $serviceName - arbitrary service name
     */
    public function reportSuccess(string $serviceName): void;

    /**
     * Return max count of failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getMaxFailures(string $serviceName): int;

    /**
     * Return critical count of failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getCriticalFailures(string $serviceName): int;

    /**
     * Return retry timeout after failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getRetryTimeout(string $serviceName): int;

    /**
     * Return count of failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getFailures(string $serviceName): int;

    /**
     * Return last failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getLastTest(string $serviceName): int;
}
