<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Tests\Unit\DataProvider;

/**
 * Class QueueFaultTolerantConsumerProvider.
 *
 * @category Tests\Unit\DataProvider
 */
class QueueFaultTolerantConsumerProvider
{
    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getNoRetryCases(): array
    {
        return [
            [
                [
                    'retryAttempts' => 1,
                    'getExtChannel' => 1,
                    'close' => 0,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 0,
                    'consume' => 1,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 0,
                ],
                10,
                1,
            ],
            [
                [
                    'retryAttempts' => 3,
                    'getExtChannel' => 1,
                    'close' => 0,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 0,
                    'consume' => 1,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 0,
                ],
                10,
                1,
            ],
            [
                [
                    'retryAttempts' => 100,
                    'getExtChannel' => 1,
                    'close' => 0,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 0,
                    'consume' => 1,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 0,
                ],
                10,
                1,
            ],
        ];
    }

    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getRetryCases(): array
    {
        return [
            [
                [
                    'retryAttempts' => 2,
                    'getExtChannel' => 2,
                    'close' => 1,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 1,
                    'consume' => 1,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 1,
                ],
                10,
                1,
            ],
            [
                [
                    'retryAttempts' => 5,
                    'getExtChannel' => 5,
                    'close' => 4,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 4,
                    'consume' => 4,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 4,
                ],
                10,
                1,
            ],
            [
                [
                    'retryAttempts' => 10,
                    'getExtChannel' => 7,
                    'close' => 6,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 6,
                    'consume' => 6,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 6,
                ],
                10,
                1,
            ],
        ];
    }

    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getThrowExAfterRetryCases(): array
    {
        return [
            [
                [
                    'retryAttempts' => 2,
                    'getExtChannel' => 3,
                    'close' => 2,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 2,
                    'consume' => 2,
                    'cancel' => 0,
                    'subscribe' => 0,
                    'warning' => 2,
                ],
                10,
                1,
            ],
            [
                [
                    'retryAttempts' => 5,
                    'getExtChannel' => 6,
                    'close' => 5,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 5,
                    'consume' => 5,
                    'cancel' => 0,
                    'subscribe' => 0,
                    'warning' => 5,
                ],
                10,
                1,
            ],
            [
                [
                    'retryAttempts' => 10,
                    'getExtChannel' => 11,
                    'close' => 10,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 10,
                    'consume' => 10,
                    'cancel' => 0,
                    'subscribe' => 0,
                    'warning' => 10,
                ],
                10,
                1,
            ],
        ];
    }

    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getHealthCheckCases(): array
    {
        return [
            [
                [
                    'retryAttempts' => 1,
                    'getExtChannel' => 1,
                    'close' => 0,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 0,
                    'consume' => 1,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 0,
                ],
                10,
                1,
            ],
            [
                [
                    'retryAttempts' => 3,
                    'getExtChannel' => 1,
                    'close' => 0,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 0,
                    'consume' => 1,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 0,
                ],
                10,
                5,
            ],
            [
                [
                    'retryAttempts' => 100,
                    'getExtChannel' => 1,
                    'close' => 0,
                    'getSubscriptionConsumer' => 0,
                    'getConsumers' => 1,
                    'isConnected' => 0,
                    'consume' => 1,
                    'cancel' => 1,
                    'subscribe' => 0,
                    'warning' => 0,
                ],
                10,
                10,
            ],
        ];
    }
}
