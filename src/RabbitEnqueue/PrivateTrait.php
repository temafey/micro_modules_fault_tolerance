<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\RabbitEnqueue;

use Closure;

/**
 * Trait PrivateTrait.
 */
trait PrivateTrait
{
    /**
     * Return closure, that can return any private or protected property value from any object.
     *
     * @param object $obj
     * @param string $attribute
     *
     * @return Closure
     */
    private function getPrivate(object $obj, string $attribute): Closure
    {
        $getter = function () use ($attribute) {
            return $this->$attribute;
        };

        return Closure::bind($getter, $obj, get_class($obj));
    }

    /**
     * Return closure ,that can set any private or protected property value in any object.
     *
     * @param object $obj
     * @param string $attribute
     *
     * @return Closure
     */
    private function setPrivate(object $obj, string $attribute): Closure
    {
        $setter = function ($value) use ($attribute): void {
            $this->$attribute = $value;
        };

        return Closure::bind($setter, $obj, get_class($obj));
    }
}
