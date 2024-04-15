<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Saga;

use MicroModule\Base\Domain\Exception\LoggerException;

/**
 * Class SagaFallResistant.
 *
 * @category Infrastructure\Utils
 */
interface SagaFallResistantInterface
{

    /**
     * Find all failed saga processes and try to resume their.
     *
     * @throws LoggerException
     */
    public function findAndResumeAttempt(): void;
}
