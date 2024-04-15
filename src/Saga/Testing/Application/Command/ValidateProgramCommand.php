<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Saga\Testing\Application\Command;

use Ramsey\Uuid\UuidInterface;

/**
 * Class ProgramValidateCommand.
 *
 * @category Testing\Application\Command
 */
class ValidateProgramCommand implements CommandInterface
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * ProgramValidateCommand constructor.
     *
     * @param UuidInterface $uuid
     */
    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
