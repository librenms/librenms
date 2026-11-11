<?php

namespace LibreNMS\Tests\Unit\Validations;

use Illuminate\Support\Facades\Redis;
use LibreNMS\Tests\TestCase;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Poller\CheckRedis;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class CheckRedisTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $cache
     */
    #[DataProvider('connections')]
    public function testChecksTheConnectionUsedForLocks(array $cache, string $expected_connection, int $expected_status): void
    {
        config(['cache' => $cache]);

        $connection = Mockery::mock();
        $connection->shouldReceive('command')->once()->with('ping')->andReturn(true);
        Redis::shouldReceive('connection')->once()->with($expected_connection)->andReturn($connection);

        $result = (new CheckRedis)->validate();

        $this->assertSame($expected_status, $result->getStatus());
        if ($expected_status === ValidationResult::SUCCESS) {
            $this->assertStringContainsString($expected_connection, $result->getMessage());
        }
    }

    /**
     * @return array<string, array{array<string, mixed>, string, int}>
     */
    public static function connections(): array
    {
        return [
            'sentinel lock connection' => [[
                'default' => 'redis',
                'stores' => ['redis' => ['driver' => 'redis', 'connection' => 'cache', 'lock_connection' => 'sentinel_cache']],
            ], 'sentinel_cache', ValidationResult::SUCCESS],
            'falls back to the store connection' => [[
                'default' => 'redis',
                'stores' => ['redis' => ['driver' => 'redis', 'connection' => 'cache']],
            ], 'cache', ValidationResult::SUCCESS],
            'falls back to default' => [[
                'default' => 'redis',
                'stores' => ['redis' => ['driver' => 'redis']],
            ], 'default', ValidationResult::SUCCESS],
            'redis store is checked when another cache driver is in use' => [[
                'default' => 'database',
                'stores' => [
                    'database' => ['driver' => 'database'],
                    'redis' => ['driver' => 'redis', 'connection' => 'cache', 'lock_connection' => 'sentinel_cache'],
                ],
            ], 'sentinel_cache', ValidationResult::WARNING],
            'named redis store' => [[
                'default' => 'fast',
                'stores' => ['fast' => ['driver' => 'redis', 'lock_connection' => 'sentinel_cache']],
            ], 'sentinel_cache', ValidationResult::SUCCESS],
        ];
    }

    public function testUnreachableRedisFailsWhenUsedAsCacheDriver(): void
    {
        config(['cache' => [
            'default' => 'redis',
            'stores' => ['redis' => ['driver' => 'redis', 'lock_connection' => 'sentinel_cache']],
        ]]);
        Redis::shouldReceive('connection')->once()->with('sentinel_cache')->andThrow(new \RuntimeException('connection refused'));

        $result = (new CheckRedis)->validate();

        $this->assertSame(ValidationResult::FAILURE, $result->getStatus());
        $this->assertStringContainsString('sentinel_cache', $result->getMessage());
    }

    public function testUnreachableRedisIsAcceptableWhenNotInUse(): void
    {
        config(['cache' => [
            'default' => 'database',
            'stores' => ['database' => ['driver' => 'database'], 'redis' => ['driver' => 'redis']],
        ]]);
        Redis::shouldReceive('connection')->once()->with('default')->andThrow(new \RuntimeException('connection refused'));

        $this->assertSame(ValidationResult::SUCCESS, (new CheckRedis)->validate()->getStatus());
    }

    public function testWarnsWhenRedisWorksButIsNotTheCacheDriver(): void
    {
        config(['cache' => [
            'default' => 'database',
            'stores' => [
                'database' => ['driver' => 'database'],
                'redis' => ['driver' => 'redis', 'lock_connection' => 'sentinel_cache'],
            ],
        ]]);
        $connection = Mockery::mock();
        $connection->shouldReceive('command')->once()->with('ping')->andReturn(true);
        Redis::shouldReceive('connection')->once()->with('sentinel_cache')->andReturn($connection);

        $this->assertSame(ValidationResult::WARNING, (new CheckRedis)->validate()->getStatus());
    }
}
