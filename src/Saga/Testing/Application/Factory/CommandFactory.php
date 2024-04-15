<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Saga\Testing\Application\Factory;

use MicroModule\Base\Domain\Exception\FactoryException;
use MicroModule\FaultTolerance\Saga\Testing\Application\Command\CommandInterface;
use MicroModule\FaultTolerance\Saga\Testing\Application\Command\InitProgramCommand;
use MicroModule\FaultTolerance\Saga\Testing\Application\Command\ValidateProgramCommand;
use Ramsey\Uuid\UuidInterface;

/**
 * Class CommandFactory.
 *
 * @category Saga\Testing\Application\Factory
 */
class CommandFactory
{
    public const PROGRAM_INIT_COMMAND = 'ProgramInitCommand';
    public const PROGRAM_VALIDATE_COMMAND = 'ProgramValidateCommand';

    /**
     * Make CommandBus command instance by constant type.
     *
     * @param mixed... $args
     *
     * @return CommandInterface
     *
     * @throws FactoryException
     */
    public function makeCommandInstanceByType(...$args): CommandInterface
    {
        $commandType = array_shift($args);

        switch ($commandType) {
            case self::PROGRAM_INIT_COMMAND:
                return $this->makeInitProgramCommand(...$args);

            case self::PROGRAM_VALIDATE_COMMAND:
                return $this->makeValidateProgramCommand(...$args);

            default:
                throw new FactoryException(sprintf('Command bus for type `%s` not found!', $commandType));
        }
    }

    /**
     * @param UuidInterface $uuid
     *
     * @return InitProgramCommand
     */
    public function makeInitProgramCommand(UuidInterface $uuid): InitProgramCommand
    {
        return new InitProgramCommand($uuid);
    }

    /**
     * @param UuidInterface $uuid
     *
     * @return ValidateProgramCommand
     */
    public function makeValidateProgramCommand(UuidInterface $uuid): ValidateProgramCommand
    {
        return new ValidateProgramCommand($uuid);
    }
}
