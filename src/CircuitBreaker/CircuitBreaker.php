<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\CircuitBreaker;

/**
 * Class CircuitBreaker.
 */
class CircuitBreaker implements CircuitBreakerInterface
{
    protected const DEFAULT_MAX_FAILURES = 3;
    protected const DEFAULT_CRITICAL_FAILURES = 6;
    protected const DEFAULT_RETRY_TIMEOUT = 1000000;

    /**
     *  Default threshold, if service fails this many times will be disabled.
     *
     * @var int
     */
    protected $defaultMaxFailures;

    /**
     *  Default threshold, if service fails this many times will be blocked.
     *
     * @var int
     */
    protected $defaultCriticalFailures;

    /**
     * How many time should we wait before retry.
     *
     * @var int
     */
    protected $defaultRetryTimeout;

    /**
     * Service avaliability failures.
     *
     * @var int[]
     */
    protected $failures = [];

    /**
     * Timestamp of last avaliability test.
     *
     * @var int[]
     */
    protected $lastTestTime = [];

    /**
     * Configure instance with storage implementation and default threshold and retry timeout.
     *
     * @param int $maxFailures      default threshold, if service fails this many times will be disabled
     * @param int $criticalFailures default threshold, if service fails this many times will be blocked
     * @param int $retryTimeout     how many seconds should we wait before retry
     */
    public function __construct(
        $maxFailures = self::DEFAULT_MAX_FAILURES,
        $criticalFailures = self::DEFAULT_CRITICAL_FAILURES,
        $retryTimeout = self::DEFAULT_RETRY_TIMEOUT
    ) {
        $this->defaultMaxFailures = $maxFailures;
        $this->defaultRetryTimeout = $retryTimeout;
        $this->defaultCriticalFailures = $criticalFailures;
    }

    /**
     * Check if service is available (according to CB knowledge).
     *
     * @param string $serviceName - arbitrary service name
     *
     * @return bool true if service is available, false if service is down
     */
    public function isAvailable(string $serviceName): bool
    {
        $failures = $this->getFailures($serviceName);
        $maxFailures = $this->getMaxFailures($serviceName);

        if ($failures < $maxFailures) {
            // this is what happens most of the time so we evaluate first
            return true;
        }

        if ($this->isBlocked($serviceName)) {
            return false;
        }
        $lastTest = $this->getLastTest($serviceName);
        $retryTimeout = $this->getRetryTimeout($serviceName);

        if ($lastTest + $retryTimeout < hrtime(true)) {
            $this->setFailures($serviceName, $failures);

            return true;
        }

        return false;
    }

    /**
     * Check if service is blocked (according to CB knowledge).
     *
     * @param string $serviceName - arbitrary service name
     *
     * @return bool true if service is available, false if service is down
     */
    public function isBlocked(string $serviceName): bool
    {
        $failures = $this->getFailures($serviceName);
        $criticalFailures = $this->getCriticalFailures($serviceName);

        return $failures >= $criticalFailures;
    }

    /**
     * Use this method to let CB know that you failed to connect to the
     * service of particular name.
     *
     * Allows CB to update its stats accordingly for future HTTP requests.
     *
     * @param string $serviceName - arbitrary service name
     */
    public function reportFailure(string $serviceName): void
    {
        $this->setFailures($serviceName, $this->getFailures($serviceName) + 1);
        $this->lastTestTime[$serviceName] = hrtime(true);
    }

    /**
     * Use this method to let CB know that you successfully connected to the
     * service of particular name.
     *
     * Allows CB to update its stats accordingly for future HTTP requests.
     *
     * @param string $serviceName - arbitrary service name
     */
    public function reportSuccess(string $serviceName): void
    {
        $failures = $this->getFailures($serviceName);
        $maxFailures = $this->getMaxFailures($serviceName);

        if ($failures > $maxFailures) {
            $this->setFailures($serviceName, $maxFailures - 1);
        } elseif ($failures > 0) {
            $this->setFailures($serviceName, $failures - 1);
        }
    }

    /**
     * Set service failures.
     *
     * @param string $serviceName
     * @param int    $failures
     */
    protected function setFailures(string $serviceName, int $failures): void
    {
        $this->failures[$serviceName] = $failures;
    }

    /**
     * Return max count of failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getMaxFailures(string $serviceName): int
    {
        return $this->defaultMaxFailures;
    }

    /**
     * Return crititak count of failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getCriticalFailures(string $serviceName): int
    {
        return $this->defaultCriticalFailures;
    }

    /**
     * Return retry timeout after failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getRetryTimeout(string $serviceName): int
    {
        return $this->defaultRetryTimeout;
    }

    /**
     * Return count of failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getFailures(string $serviceName): int
    {
        if (!isset($this->failures[$serviceName])) {
            $this->failures[$serviceName] = 0;
        }

        return $this->failures[$serviceName];
    }

    /**
     * Return last failed request.
     *
     * @param string $serviceName
     *
     * @return int
     */
    public function getLastTest(string $serviceName): int
    {
        if (!isset($this->lastTestTime[$serviceName])) {
            $this->lastTestTime[$serviceName] = hrtime(true);
        }

        return $this->lastTestTime[$serviceName];
    }
}
