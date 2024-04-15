<?php

declare(strict_types=1);

namespace AdgoalCommon\FaultTolerance\Tests\Unit\RedisAlerting;

use AdgoalCommon\Alerting\Domain\Exception\StorageException;
use AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository;
use AdgoalCommon\FaultTolerance\Tests\Unit\RepositoryTestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Redis;
use RedisException;

/**
 * Class AlertingStorageFaultTolerantRepositoryTest.
 *
 * @category Tests\Unit\Infrastructure\Service
 */
class AlertingStorageFaultTolerantRepositoryTest extends RepositoryTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::get
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::set
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::save
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\AlertingStorageFaultTolerantRepositoryProvider::getNoRetryCases()
     *
     * @param string  $key
     * @param string  $item
     * @param mixed[] $config
     * @param mixed[] $times
     *
     * @throws StorageException
     * @throws RedisException
     */
    public function ifConnectionToRedisExistsNoRetryTest(string $key, string $item, array $config, array $times): void
    {
        $redisMock = $this->getRedisMock(false, $times, $item, '');
        $alertingStorageFaultTolerantRepository = new AlertingStorageFaultTolerantRepository(
            $redisMock,
            $config,
            $times['retryAttempts'],
            1
        );

        $result = $alertingStorageFaultTolerantRepository->get($key);
        self::assertEquals($item, $result);

        $result = $alertingStorageFaultTolerantRepository->set($key, $item);
        self::assertTrue($result);

        $result = $alertingStorageFaultTolerantRepository->save();
        self::assertTrue($result);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::get
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\AlertingStorageFaultTolerantRepositoryProvider::getRetryCases()
     *
     * @param string  $key
     * @param string  $item
     * @param mixed[] $config
     * @param mixed[] $times
     * @param string  $exception
     *
     * @throws RedisException
     * @throws StorageException
     */
    public function getFromRedisExistsAfterNextRetryTest(
        string $key,
        string $item,
        array $config,
        array $times,
        string $exception
    ): void {
        $redisMock = $this->getRedisMock(true, $times, $item, $exception);
        $alertingStorageFaultTolerantRepository = new AlertingStorageFaultTolerantRepository(
            $redisMock,
            $config,
            $times['retryAttempts'],
            1
        );
        $loggerMock = $this->makeLoggerMock($times);
        $alertingStorageFaultTolerantRepository->setLogger($loggerMock);

        $result = $alertingStorageFaultTolerantRepository->get($key);
        self::assertEquals($item, $result);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::set
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\AlertingStorageFaultTolerantRepositoryProvider::getSetRetryCases()
     *
     * @param string  $key
     * @param string  $item
     * @param mixed[] $config
     * @param mixed[] $times
     * @param string  $exception
     *
     * @throws RedisException
     * @throws StorageException
     */
    public function setFromRedisExistsAfterNextRetryTest(
        string $key,
        string $item,
        array $config,
        array $times,
        string $exception
    ): void {
        $redisMock = $this->getRedisMock(true, $times, $item, $exception);
        $alertingStorageFaultTolerantRepository = new AlertingStorageFaultTolerantRepository(
            $redisMock,
            $config,
            $times['retryAttempts'],
            1
        );
        $loggerMock = $this->makeLoggerMock($times);
        $alertingStorageFaultTolerantRepository->setLogger($loggerMock);

        $result = $alertingStorageFaultTolerantRepository->set($key, $item);
        self::assertTrue($result);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::save
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\AlertingStorageFaultTolerantRepositoryProvider::getSaveRetryCases()
     *
     * @param string  $key
     * @param string  $item
     * @param mixed[] $config
     * @param mixed[] $times
     * @param string  $exception
     *
     * @throws RedisException
     * @throws StorageException
     */
    public function saveFromRedisExistsAfterNextRetryTest(
        string $key,
        string $item,
        array $config,
        array $times,
        string $exception
    ): void {
        $redisMock = $this->getRedisMock(true, $times, $item, $exception);
        $alertingStorageFaultTolerantRepository = new AlertingStorageFaultTolerantRepository(
            $redisMock,
            $config,
            $times['retryAttempts'],
            1
        );
        $loggerMock = $this->makeLoggerMock($times);
        $alertingStorageFaultTolerantRepository->setLogger($loggerMock);

        $result = $alertingStorageFaultTolerantRepository->save();
        self::assertTrue($result);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::get
     *  @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::runFaultTolerantProcess
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::connect
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\AlertingStorageFaultTolerantRepositoryProvider::getThrowExAfterGetAndAllRetryCases()
     *
     * @param string  $key
     * @param string  $item
     * @param mixed[] $config
     * @param mixed[] $times
     * @param string  $exception
     *
     * @throws RedisException
     * @throws StorageException
     */
    public function throwExceptionIfConnectionToRedisNotExistsAfterGetAndAllRetryTest(
        string $key,
        string $item,
        array $config,
        array $times,
        string $exception
    ): void {
        $this->expectException($exception);

        $redisMock = $this->getRedisMock(true, $times, $item, $exception);
        $alertingStorageFaultTolerantRepository = new AlertingStorageFaultTolerantRepository(
            $redisMock,
            $config,
            $times['retryAttempts'],
            1
        );
        $loggerMock = $this->makeLoggerMock($times);
        $alertingStorageFaultTolerantRepository->setLogger($loggerMock);
        $alertingStorageFaultTolerantRepository->get($key);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::set
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\AlertingStorageFaultTolerantRepositoryProvider::getThrowExAfterSetAndAllRetryCases()
     *
     * @param string  $key
     * @param string  $item
     * @param mixed[] $config
     * @param mixed[] $times
     * @param string  $exception
     *
     * @throws RedisException
     * @throws StorageException
     */
    public function throwExceptionIfConnectionToRedisNotExistsAfterSetAndAllRetryTest(
        string $key,
        string $item,
        array $config,
        array $times,
        string $exception
    ): void {
        $this->expectException($exception);

        $redisMock = $this->getRedisMock(true, $times, $item, $exception);
        $alertingStorageFaultTolerantRepository = new AlertingStorageFaultTolerantRepository(
            $redisMock,
            $config,
            $times['retryAttempts'],
            1
        );
        $loggerMock = $this->makeLoggerMock($times);
        $alertingStorageFaultTolerantRepository->setLogger($loggerMock);
        $alertingStorageFaultTolerantRepository->set($key, $item);
    }

    /**
     * @test
     *
     * @group unit
     *
     * @covers       \AdgoalCommon\FaultTolerance\RedisAlerting\AlertingStorageFaultTolerantRepository::save
     *
     * @dataProvider \AdgoalCommon\FaultTolerance\Tests\Unit\DataProvider\AlertingStorageFaultTolerantRepositoryProvider::getThrowExAfterSaveAndAllRetryCases()
     *
     * @param string  $key
     * @param string  $item
     * @param mixed[] $config
     * @param mixed[] $times
     * @param string  $exception
     *
     * @throws RedisException
     * @throws StorageException
     */
    public function throwExceptionIfConnectionToRedisNotExistsAfterSaveAndAllRetryTest(
        string $key,
        string $item,
        array $config,
        array $times,
        string $exception
    ): void {
        $this->expectException($exception);

        $redisMock = $this->getRedisMock(true, $times, $item, $exception);
        $alertingStorageFaultTolerantRepository = new AlertingStorageFaultTolerantRepository(
            $redisMock,
            $config,
            $times['retryAttempts'],
            1
        );
        $loggerMock = $this->makeLoggerMock($times);
        $alertingStorageFaultTolerantRepository->setLogger($loggerMock);
        $alertingStorageFaultTolerantRepository->save();
    }

    /**
     * Return Redis mock object.
     *
     * @param bool    $exceptional
     * @param mixed[] $times
     * @param string  $item
     * @param string  $exception
     *
     * @return MockInterface|Redis
     */
    protected function getRedisMock(bool $exceptional = true, array $times, string $item, string $exception): MockInterface
    {
        $redisMock = Mockery::mock(Redis::class);
        $redisMock
            ->shouldReceive('connect')
            ->times($times['connect'])
            ->andReturn(true);

        if (true === $exceptional && $times['get'] > 0) {
            $redisMock
                ->shouldReceive('get')
                ->times($times['get'])
                ->andThrow($exception)
                ->shouldReceive('get')
                ->zeroOrMoreTimes()
                ->andReturn($item);
        } else {
            $redisMock
                ->shouldReceive('get')
                ->times($times['get'])
                ->andReturn($item);
        }

        if (true === $exceptional && $times['set'] > 0) {
            $redisMock
                ->shouldReceive('set')
                ->times($times['set'])
                ->andThrow($exception)
                ->shouldReceive('set')
                ->zeroOrMoreTimes()
                ->andReturn(true);
        } else {
            $redisMock
                ->shouldReceive('set')
                ->times($times['set'])
                ->andReturn(true);
        }

        if (true === $exceptional && $times['save'] > 0) {
            $redisMock
                ->shouldReceive('save')
                ->times($times['save'])
                ->andThrow($exception)
                ->shouldReceive('save')
                ->zeroOrMoreTimes()
                ->andReturn(true);
        } else {
            $redisMock
                ->shouldReceive('save')
                ->times($times['save'])
                ->andReturn(true);
        }

        return $redisMock;
    }

    /**
     * Return Logger mock object.
     *
     * @param mixed[] $times
     *
     * @return MockInterface|LoggerInterface
     */
    protected function makeLoggerMock(array $times): MockInterface
    {
        $loggerMock = Mockery::mock(LoggerInterface::class);
        $loggerMock
            ->shouldReceive('warning')
            ->times($times['warning'])
            ->andReturn('');
        $loggerMock
            ->shouldReceive('debug')
            ->times($times['debug'])
            ->andReturn('');

        return $loggerMock;
    }
}
