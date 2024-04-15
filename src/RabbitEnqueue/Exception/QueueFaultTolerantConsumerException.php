<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\RabbitEnqueue\Exception;

use MicroModule\Base\Domain\Exception\CriticalException;

/**
 * Class QueueFaultTolerantConsumerException.
 *
 * @category Domain\Exception\Program
 */
final class QueueFaultTolerantConsumerException extends CriticalException
{
}
