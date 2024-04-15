<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\Saga;

use AdgoalCommon\Base\Infrastructure\Testing\ValueObjectMockTrait;
use AdgoalCommon\FaultTolerance\Saga\SagaFallResistant;
use AdgoalCommon\FaultTolerance\Saga\Testing\Application\Command\ValidateProgramCommand;
use AdgoalCommon\FaultTolerance\Saga\Testing\Application\Factory\CommandFactory;
use AdgoalCommon\FaultTolerance\Saga\Testing\Domain\Event\ProgramWasInitEvent;
use AdgoalCommon\FaultTolerance\Saga\Testing\TestingSaga;
use AdgoalCommon\Saga\Testing\Scenario;
use AdgoalCommon\Saga\Testing\TraceableCommandBus;
use Broadway\Domain\DateTime;
use Broadway\Domain\DomainEventStream;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventDispatcher\CallableEventDispatcher;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\Saga\Metadata\StaticallyConfiguredSagaMetadataFactory;
use Broadway\Saga\MultipleSagaManager;
use Broadway\Saga\State\InMemoryRepository;
use Broadway\Saga\State\StateManager;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use InvalidArgumentException;
use League\Tactician\CommandBus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class SagaFallResistantTest.
 *
 * @category Tests\Unit\Application\Command
 */
class SagaFallResistantTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Test saga scenario object.ProgramCommand.
     *
     * @var Scenario|null
     */
    protected $scenario;

    /**
     * Saga fall resistant service.
     *
     * @var SagaFallResistant|null
     */
    protected $fallResistant;

    /**
     * Tested saga.
     *
     * @var TestingSaga|null
     */
    protected $saga;

    /**
     * @var TraceableEventStore|null
     */
    protected $eventStore;

    /**
     * CommandFactory mock object.
     *
     * @var MockInterface
     */
    protected $commandFactoryMock;

    /**
     * Initialize and saga test scenario object and fall resistant service.
     */
    protected function setUp(): void
    {
        $traceableCommandBus = new TraceableCommandBus();
        $traceableCommandBus->record();
        $this->saga = $this->createSaga($traceableCommandBus);
        $sagaStateRepository = new InMemoryRepository();
        $sagaManager = new MultipleSagaManager(
            $sagaStateRepository,
            [$this->saga],
            new StateManager($sagaStateRepository, new Version4Generator()),
            new StaticallyConfiguredSagaMetadataFactory(),
            new CallableEventDispatcher()
        );
        $this->scenario = new Scenario($this, $sagaManager, $traceableCommandBus);
        $this->eventStore = new TraceableEventStore(new InMemoryEventStore());
        $this->eventStore->trace();
        $this->fallResistant = new SagaFallResistant($sagaStateRepository, $this->eventStore, $sagaManager);
    }

    /**
     * Initialize saga object.
     *
     * @param CommandBus $commandBus
     *
     * @return TestingSaga
     */
    protected function createSaga(CommandBus $commandBus): TestingSaga
    {
        $this->commandFactoryMock = Mockery::mock(CommandFactory::class);

        return new TestingSaga($commandBus, $this->commandFactoryMock);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\Saga\SagaFallResistant::findAndResumeAttempt
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\SagaFallResistantDataProvider::getData
     *
     * @param string $uuid Afm process unique id
     *
     * @throws \AdgoalCommon\Base\Domain\Exception\LoggerException
     */
    public function fallResistantShouldResumeSagaProcessAfterExceptionTest(string $uuid): void
    {
        $validateCommandMock = $this->createValidateCommandMock($uuid);
        //Create CommandFactory mock,
        //that after first call of method makeCommandInstanceByType throw exception,
        //after second call return mocked ConvertCommand
        $this->commandFactoryMock
            ->shouldReceive('makeCommandInstanceByType')
            ->once()
            ->andThrow(InvalidArgumentException::class)
            ->shouldReceive('makeCommandInstanceByType')
            ->once()
            ->andReturn($validateCommandMock);
        $uuidMock = $this->createUuidMock($uuid, 2);
        $programWasInitEvent = new ProgramWasInitEvent($uuidMock);
        //Run first scenario in saga
        $this->scenario
            ->given([
                $programWasInitEvent,
            ]);

        $lastState = $this->saga->getLastState();
        //Assert, that after run saga process we get failed state
        $this->assertTrue($lastState->isFailed());

        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($uuid, $programWasInitEvent, 0),
        ]);
        $this->eventStore->append($uuid, $domainEventStream);

        //Run fall resistant process
        $this->fallResistant->findAndResumeAttempt();
        //get last saga state to analyze if the process was successful
        $lastState = $this->saga->getLastState();
        $this->assertTrue($lastState->isInProgress());
        $this->assertEquals(1, $lastState->get(SagaFallResistant::SAGA_FALL_RESISTANT_FAILED_ATTEMPT_KEY));
        $this->scenario
            ->then([
                $validateCommandMock,
            ]);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\Saga\SagaFallResistant::findAndResumeAttempt
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\SagaFallResistantDataProvider::getData
     *
     * @param string $uuid Afm process unique id
     *
     * @throws \AdgoalCommon\Base\Domain\Exception\LoggerException
     */
    public function fallResistantShouldSetDiedStatusToSagaStateAfterMaxAttemptTest(string $uuid): void
    {
        //Create CommandFactory mock,
        //that after call of method makeCommandInstanceByType throw exception
        $this->commandFactoryMock
            ->shouldReceive('makeCommandInstanceByType')
            ->zeroOrMoreTimes()
            ->andThrow(InvalidArgumentException::class);
        $uuidMock = $this->createUuidMock($uuid, 3);
        $programWasInitEvent = new ProgramWasInitEvent($uuidMock);
        //Run first scenario in saga
        $this->scenario
            ->given([
                $programWasInitEvent,
            ]);

        $lastState = $this->saga->getLastState();
        //Assert, that after run saga process we get failed state
        $this->assertTrue($lastState->isFailed());

        $domainEventStream = new DomainEventStream([
            $this->createDomainMessage($uuid, $programWasInitEvent, 0),
        ]);
        $this->eventStore->append($uuid, $domainEventStream);

        //Run fall resistant process
        $this->fallResistant->findAndResumeAttempt();
        //get last saga state to analyze if the process was successful
        $lastState = $this->saga->getLastState();
        $this->assertTrue($lastState->isFailed());
        $this->assertEquals(1, $lastState->get(SagaFallResistant::SAGA_FALL_RESISTANT_FAILED_ATTEMPT_KEY));

        //Run fall resistant process
        $this->fallResistant->findAndResumeAttempt();
        //get last saga state to analyze if the process was successful
        $lastState = $this->saga->getLastState();
        $this->assertTrue($lastState->isDied());
        $this->assertEquals(2, $lastState->get(SagaFallResistant::SAGA_FALL_RESISTANT_FAILED_ATTEMPT_KEY));
        $this->assertEquals(SagaFallResistant::SAGA_FALL_RESISTANT_MAX_ATTEMPT, $lastState->get(SagaFallResistant::SAGA_FALL_RESISTANT_FAILED_ATTEMPT_KEY));
    }

    /**
     * Create and return ConvertCommand mocked object.
     *
     * @param string $uuid
     *
     * @return MockInterface
     */
    protected function createValidateCommandMock(string $uuid): MockInterface
    {
        $validateCommandMock = Mockery::mock(ValidateProgramCommand::class);
        $validateCommandMock
            ->shouldReceive('getUuid')
            ->zeroOrMoreTimes()
            ->andReturn($this->createUuidMock($uuid));

        return $validateCommandMock;
    }

    /**
     * Create and return DomainMessage object.
     *
     * @param string              $id
     * @param ProgramWasInitEvent $event
     * @param int                 $playhead
     * @param DateTime|null       $recordedOn
     *
     * @return DomainMessage
     */
    protected function createDomainMessage(
        string $id,
        ProgramWasInitEvent $event,
        int $playhead,
        ?DateTime $recordedOn = null
    ): DomainMessage {
        return new DomainMessage($id, $playhead, new MetaData([]), $event, $recordedOn ?: DateTime::now());
    }

    /**
     * This method is called after each test.
     */
    protected function tearDown(): void
    {
        $this->fallResistant = null;
        $this->scenario = null;
        $this->saga = null;
        $this->commandFactoryMock = null;
    }
}
