<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider;

use AdgoalCommon\Alerting\Domain\Exception\StorageException;
use RedisException;

/**
 * Class AlertingStorageFaultTolerantRepositoryProvider.
 *
 * @category Tests\Unit\DataProvider
 */
class AlertingStorageFaultTolerantRepositoryProvider
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
                'test-key',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 1,
                    'connect' => 1,
                    'get' => 1,
                    'set' => 1,
                    'save' => 1,
                    'warning' => 0,
                ],
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 5,
                    'connect' => 1,
                    'get' => 1,
                    'set' => 1,
                    'save' => 1,
                    'warning' => 0,
                ],
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
                'test-key',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 2,
                    'connect' => 2,
                    'get' => 1,
                    'set' => 0,
                    'save' => 0,
                    'warning' => 1,
                    'debug' => 1,
                ],
                StorageException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 2,
                    'connect' => 2,
                    'get' => 1,
                    'set' => 0,
                    'save' => 0,
                    'warning' => 1,
                    'debug' => 1,
                ],
                RedisException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 10,
                    'connect' => 10,
                    'get' => 9,
                    'set' => 0,
                    'save' => 0,
                    'warning' => 9,
                    'debug' => 9,
                ],
                RedisException::class,
            ],
        ];
    }

    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getSetRetryCases(): array
    {
        return [
            [
                'test-key',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 2,
                    'connect' => 2,
                    'get' => 0,
                    'set' => 1,
                    'save' => 0,
                    'warning' => 1,
                    'debug' => 1,
                ],
                StorageException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 2,
                    'connect' => 2,
                    'get' => 0,
                    'set' => 1,
                    'save' => 0,
                    'warning' => 1,
                    'debug' => 1,
                ],
                RedisException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 5,
                    'connect' => 5,
                    'get' => 0,
                    'set' => 4,
                    'save' => 0,
                    'warning' => 4,
                    'debug' => 4,
                ],
                RedisException::class,
            ],
        ];
    }

    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getSaveRetryCases(): array
    {
        return [
            [
                'test-key',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 2,
                    'connect' => 2,
                    'get' => 0,
                    'set' => 0,
                    'save' => 1,
                    'warning' => 1,
                    'debug' => 1,
                ],
                StorageException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 2,
                    'connect' => 2,
                    'get' => 0,
                    'set' => 0,
                    'save' => 1,
                    'warning' => 1,
                    'debug' => 1,
                ],
                RedisException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 3,
                    'connect' => 3,
                    'get' => 0,
                    'set' => 0,
                    'save' => 2,
                    'warning' => 2,
                    'debug' => 2,
                ],
                RedisException::class,
            ],
        ];
    }

    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getThrowExAfterGetAndAllRetryCases(): array
    {
        return [
            [
                'test-key',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 1,
                    'connect' => 1,
                    'get' => 1,
                    'set' => 0,
                    'save' => 0,
                    'warning' => 1,
                    'debug' => 0,
                ],
                StorageException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 1,
                    'connect' => 1,
                    'get' => 1,
                    'set' => 0,
                    'save' => 0,
                    'warning' => 1,
                    'debug' => 0,
                ],
                RedisException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 5,
                    'connect' => 5,
                    'get' => 5,
                    'set' => 0,
                    'save' => 0,
                    'warning' => 5,
                    'debug' => 4,
                ],
                RedisException::class,
            ],
        ];
    }

    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getThrowExAfterSetAndAllRetryCases(): array
    {
        return [
            [
                'test-key',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 1,
                    'connect' => 1,
                    'get' => 0,
                    'set' => 1,
                    'save' => 0,
                    'warning' => 1,
                    'debug' => 0,
                ],
                StorageException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 1,
                    'connect' => 1,
                    'get' => 0,
                    'set' => 1,
                    'save' => 0,
                    'warning' => 1,
                    'debug' => 0,
                ],
                RedisException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 5,
                    'connect' => 5,
                    'get' => 0,
                    'set' => 5,
                    'save' => 0,
                    'warning' => 5,
                    'debug' => 4,
                ],
                RedisException::class,
            ],
        ];
    }

    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getThrowExAfterSaveAndAllRetryCases(): array
    {
        return [
            [
                'test-key',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 1,
                    'connect' => 1,
                    'get' => 0,
                    'set' => 0,
                    'save' => 1,
                    'warning' => 1,
                    'debug' => 0,
                ],
                StorageException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 1,
                    'connect' => 1,
                    'get' => 0,
                    'set' => 0,
                    'save' => 1,
                    'warning' => 1,
                    'debug' => 0,
                ],
                RedisException::class,
            ],
            [
                'test-key-2',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                [
                    'host' => 'test',
                    'port' => 1,
                ],
                [
                    'retryAttempts' => 5,
                    'connect' => 5,
                    'get' => 0,
                    'set' => 0,
                    'save' => 5,
                    'warning' => 5,
                    'debug' => 4,
                ],
                RedisException::class,
            ],
        ];
    }
}
