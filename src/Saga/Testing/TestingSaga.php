<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Saga\Testing;

use MicroModule\FaultTolerance\Saga\Testing\Application\Factory\CommandFactory;
use MicroModule\FaultTolerance\Saga\Testing\Domain\Event\ProgramWasInitEvent;
use MicroModule\FaultTolerance\Saga\Testing\Domain\Event\ProgramWasValidatedEvent;
use MicroModule\Saga\AbstractSaga;
use Broadway\Saga\Metadata\StaticallyConfiguredSagaInterface;
use Broadway\Saga\State;
use Broadway\Saga\State\Criteria;
use League\Tactician\CommandBus;
use Throwable;

/**
 * Class TestingSaga.
 *
 * @category Saga\Testing
 */
class TestingSaga extends AbstractSaga implements StaticallyConfiguredSagaInterface
{
    private const STATE_CRITERIA_KEY = 'id';

    /**
     * Checks whether exception is caught.
     *
     * @var bool
     */
    protected $exceptionCaught = true;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var CommandFactory
     */
    private $commandFactory;

    /**
     * TestingSaga constructor.
     *
     * @param CommandBus     $commandBus
     * @param CommandFactory $commandFactory
     */
    public function __construct(CommandBus $commandBus, CommandFactory $commandFactory)
    {
        $this->commandBus = $commandBus;
        $this->commandFactory = $commandFactory;
    }

    /**
     * @return mixed[]
     */
    public static function configuration(): array
    {
        return [
            'ProgramWasInitEvent' => static function () {
                return null;
            },
            'ProgramWasValidatedEvent' => static function (ProgramWasValidatedEvent $event) {
                return new Criteria([self::STATE_CRITERIA_KEY => $event->getUuid()->toString()]);
            },
        ];
    }

    /**
     * @param State               $state
     * @param ProgramWasInitEvent $event
     *
     * @return State
     */
    public function handleProgramWasInitEvent(State $state, ProgramWasInitEvent $event): State
    {
        $state->set(self::STATE_CRITERIA_KEY, $event->getUuid()->toString());
        $command = $this->commandFactory->makeCommandInstanceByType(
            CommandFactory::PROGRAM_INIT_COMMAND,
            $event->getUuid()
        );

        try {
            $this->commandBus->handle($command);
        } catch (Throwable $e) {
            $state->setDone();
        }

        return $state;
    }

    /**
     * @param State $state
     *
     * @return State
     */
    public function handleProgramWasValidatedEvent(State $state): State
    {
        return $state->setDone();
    }
}
