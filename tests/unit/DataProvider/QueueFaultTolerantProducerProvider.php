<?php

declare(strict_types=1);

namespace MicroModule\FaultTolerance\Tests\Unit\DataProvider;

/**
 * Class QueueFaultTolerantProducerProvider.
 *
 * @category Tests\Unit\DataProvider
 */
class QueueFaultTolerantProducerProvider
{
    /**
     * Return data fixture.
     *
     * @return mixed[]
     */
    public function getData(): array
    {
        return [
            [
                'test-topic',
                '54977||www.cooperandkid.com|Online|Family|15%|||Yes|https://www.shareasale.com/r.cfm?b=638938&u=742098&m=54977|||||',
                2,
                10,
            ],
            [
                'test-topic-2',
                '81343| Accent Benefits - (Zip Properties, Inc.)|AccentHealthBenefits.com|Online|Health|35%|||Yes|https://www.shareasale.com/r.cfm?b=1217752&u=742098&m=81343|||||',
                3,
                10,
            ],
            [
                'test-topic-3',
                '83500|\'47Brand|www.47brand.com|Online|Clothing|7%|||Yes|https://www.shareasale.com/r.cfm?b=1258568&u=742098&m=83500|||||',
                1,
                10,
            ],
        ];
    }
}
