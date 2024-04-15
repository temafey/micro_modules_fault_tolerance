<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Saga\Testing\Application\Command;

use Ramsey\Uuid\UuidInterface;

/**
 * Class ProgramInitCommand.
 */
class InitProgramCommand implements CommandInterface
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * ProgramInitCommand constructor.
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
