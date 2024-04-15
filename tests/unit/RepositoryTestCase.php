<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Tests\Unit;

use MicroModule\Base\Infrastructure\Testing\ValueObjectMockTrait;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventHandling\TraceableEventBus;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use PHPUnit\Framework\TestCase;

/**
 * Class RepositoryTestCase.
 *
 * @category Tests\Unit\Infrastructure\Repository
 */
class RepositoryTestCase extends TestCase
{
    use ValueObjectMockTrait;

    /** @var TraceableEventBus */
    protected $eventBus;

    /** @var TraceableEventStore */
    protected $eventStore;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->eventBus = new TraceableEventBus(new SimpleEventBus());
        $this->eventBus->trace();

        $this->eventStore = new TraceableEventStore(new InMemoryEventStore());
        $this->eventStore->trace();
    }
}
