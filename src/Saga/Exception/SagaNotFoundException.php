<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Saga\Exception;

use MicroModule\Base\Domain\Exception\CriticalException;

/**
 * Class SagaNotFoundException.
 *
 * @category Infrastructure\Utils
 */
class SagaNotFoundException extends CriticalException
{
}
