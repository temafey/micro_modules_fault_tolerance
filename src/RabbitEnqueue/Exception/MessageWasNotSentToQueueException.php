<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\RabbitEnqueue\Exception;

use MicroModule\Base\Domain\Exception\CriticalException;

/**
 * Class MessageWasNotSentToQueueException.
 *
 * @category Domain\Exception\Program
 */
final class MessageWasNotSentToQueueException extends CriticalException
{
}
