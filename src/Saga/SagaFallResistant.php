<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Saga;

use MicroModule\Base\Domain\Exception\LoggerException;
use MicroModule\Base\Utils\LoggerTrait;
use MicroModule\FaultTolerance\Saga\Exception\SagaNotFoundException;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\EventStore\EventStore;
use Broadway\Saga\MultipleSagaManager;
use Broadway\Saga\SagaInterface;
use Broadway\Saga\State;
use Broadway\Saga\State\RepositoryInterface;
use Throwable;

/**
 * Class SagaFallResistant.
 *
 * @category Infrastructure\Utils
 */
class SagaFallResistant
{
    use LoggerTrait;

    public const SAGA_FALL_RESISTANT_FAILED_ATTEMPT_KEY = 'failedAttempt';
    public const SAGA_FALL_RESISTANT_MAX_ATTEMPT = 2;

    /**
     * SagaFallResistant constructor.
     *
     * @param RepositoryInterface $sagaRepository
     * @param EventStore          $eventStore
     * @param MultipleSagaManager $sagaManagerEventListener
     */
    public function __construct(
        protected RepositoryInterface $sagaRepository,
        protected EventStore $eventStore,
        protected MultipleSagaManager $sagaManagerEventListener
    ) {}

    /**
     * Find all failed saga processes and try to resume their.
     *
     * @throws LoggerException
     */
    public function findAndResumeAttempt(): void
    {
        $failedStates = $this->sagaRepository->findFailed();
        $this->logMessage(sprintf('Fall resistant: Found %s failed saga program states.', count($failedStates)), LOG_INFO);

        foreach ($failedStates as $state) {
            /** @var State $state */
            $processId = (string) $state->get('id');
            $lastEvent = $this->getLastCommittedEvent($processId);
            $this->handleActionFromLastEvent($lastEvent, $state);
        }
    }

    /**
     * Find and return last committed process event.
     *
     * @param string $id
     *
     * @return DomainMessage
     */
    private function getLastCommittedEvent(string $id): DomainMessage
    {
        $committedEvents = $this->eventStore->load($id);

        return $this->getLastEvent($committedEvents);
    }

    /**
     * Return last event from event stream object.
     *
     * @param DomainEventStream $committedEvents
     *
     * @return DomainMessage
     */
    private function getLastEvent(DomainEventStream $committedEvents): DomainMessage
    {
        $events = iterator_to_array($committedEvents);

        return end($events);
    }

    /**
     * Handle last event action in saga scenario.
     *
     * @param DomainMessage $domainMessage
     * @param State         $state
     *
     * @throws LoggerException
     */
    private function handleActionFromLastEvent(DomainMessage $domainMessage, State $state): void
    {
        $sagaId = $state->getSagaId();

        try {
            $saga = $this->sagaManagerEventListener->getSagaByType($sagaId);

            if (null === $saga) {
                throw new SagaNotFoundException(sprintf('Saga with type \'%s\' not found in SagaManager', $sagaId));
            }
            $failedAttempt = $state->get(self::SAGA_FALL_RESISTANT_FAILED_ATTEMPT_KEY) ?? 0;
            $state->set(self::SAGA_FALL_RESISTANT_FAILED_ATTEMPT_KEY, ++$failedAttempt);
            $this->logMessage(sprintf('Fall resistant: Program  \'%s\' \'%s\' attempt.', $state->getId(), $failedAttempt), LOG_INFO);

            $state = $this->handleEvent($state, $saga, $domainMessage);
        } catch (Throwable $e) {
            $state->setDied();
            $this->sagaRepository->save($state);
            $this->logMessage(sprintf('Fall resistant: Program  \'%s\' set \'died\' status.', $state->getId()), LOG_INFO);

            return;
        }

        if ($state->isFailed()) {
            $failedAttempt = $state->get(self::SAGA_FALL_RESISTANT_FAILED_ATTEMPT_KEY);

            if (self::SAGA_FALL_RESISTANT_MAX_ATTEMPT === $failedAttempt) {
                $this->logMessage(sprintf('Fall resistant: Program  \'%s\' set \'died\' status after \'%s\'  attempts.', $state->getId(), self::SAGA_FALL_RESISTANT_MAX_ATTEMPT), LOG_INFO);
                $state->setDied();
            }
            $this->sagaRepository->save($state);
        }

        if ($state->isDone()) {
            $this->logMessage(sprintf('Fall resistant: Program  \'%s\' is done after \'%s\'  attempts.', $state->getId(), $failedAttempt), LOG_INFO);
        }
    }

    /**
     * Handle event in saga object.
     *
     * @param State         $state
     * @param SagaInterface $saga
     * @param DomainMessage $domainMessage
     *
     * @return State
     */
    private function handleEvent(State $state, SagaInterface $saga, DomainMessage $domainMessage): State
    {
        $state->setInProgress();
        $this->sagaRepository->save($state);

        return $saga->handle($state, $domainMessage);
    }
}
