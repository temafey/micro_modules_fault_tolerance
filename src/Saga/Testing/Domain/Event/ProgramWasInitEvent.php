<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Saga\Testing\Domain\Event;

use MicroModule\Base\Domain\Exception\InvalidDataException;
use Assert\Assertion;
use Broadway\Serializer\Serializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class ProgramWasInitEvent.
 *
 * @category Saga\Testing\Domain\Event
 */
class ProgramWasInitEvent implements Serializable
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * ProgramWasInitEvent constructor.
     *
     * @param UuidInterface $uuid
     */
    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Deserialize Event.
     *
     * @param mixed[] $data
     *
     * @return ProgramWasInitEvent
     *
     * @throws InvalidDataException
     */
    public static function deserialize(array $data): self
    {
        Assertion::keyExists($data, 'uuid');

        return new static(
            Uuid::fromString($data['uuid']),
            );
    }

    /**
     * Serialize Event.
     *
     * @return mixed[]
     *
     * @throws InvalidDataException
     */
    public function serialize(): array
    {
        return [
            'uuid' => $this->getUuid()->toString(),
        ];
    }

    /**
     * Get Uuid.
     *
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }
}
