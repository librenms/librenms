<?php

namespace LibreNMS\Tests\Unit\Horizon;

use App\Facades\LibrenmsConfig;
use App\Horizon\DatastoreMetricsRepository;
use App\Models\Device;
use Laravel\Horizon\Contracts\MetricsRepository;
use LibreNMS\Data\Store\Datastore;
use LibreNMS\Tests\TestCase;
use Mockery;
use Mockery\MockInterface;

final class DatastoreMetricsRepositoryTest extends TestCase
{
    public function testBoundAsHorizonMetricsRepository(): void
    {
        $this->assertInstanceOf(DatastoreMetricsRepository::class, $this->app->make(MetricsRepository::class));
    }

    public function testAggregatesJobsByClass(): void
    {
        $result = DatastoreMetricsRepository::aggregate([
            'job:PollDevice:1' => ['throughput' => '3', 'runtime' => '1000'],
            'job:PollDevice:2' => ['throughput' => '1', 'runtime' => '5000'],
            'job:App\Jobs\DispatchPollingWork' => ['throughput' => '30', 'runtime' => '12.5'],
            'queue:poll' => ['throughput' => '4', 'runtime' => '2000'],
            'queue:poll-2' => ['throughput' => '2', 'runtime' => '300'],
        ]);

        $this->assertSame([
            'job' => [
                'PollDevice' => ['throughput' => 4, 'runtime' => 2000.0],
                'DispatchPollingWork' => ['throughput' => 30, 'runtime' => 12.5],
            ],
            'queue' => [
                'poll' => ['throughput' => 4, 'runtime' => 2000.0],
                'poll-2' => ['throughput' => 2, 'runtime' => 300.0],
            ],
        ], $result);
    }

    public function testIdleRuntimeIsUnknown(): void
    {
        $result = DatastoreMetricsRepository::aggregate([
            'job:DiscoverDevice:1' => ['throughput' => null, 'runtime' => null],
        ]);

        $this->assertSame(['job' => ['DiscoverDevice' => ['throughput' => 0, 'runtime' => null]]], $result);
    }

    public function testWritesToDatastore(): void
    {
        LibrenmsConfig::set('rrd.enable', false);

        $this->app->instance('Datastore', Mockery::mock(Datastore::class, function (MockInterface $mock): void {
            $mock->shouldReceive('write')->once()->withArgs(fn (string $measurement, array $fields, array $tags, array $meta): bool => $measurement === 'horizon'
                && $fields === ['throughput' => 2, 'runtime' => 150.0]
                && $tags === ['type' => 'queue', 'name' => 'discovery']
                && $meta['rrd_name'] === ['horizon', 'queue', 'discovery']
                && $meta['device'] instanceof Device
                && $meta['device']->hostname === DatastoreMetricsRepository::HOSTNAME
                && $meta['rrd_def']->isValidDataset('throughput')
                && $meta['rrd_def']->isValidDataset('runtime'));
        }));

        $this->app->make(DatastoreMetricsRepository::class)->writeToDatastore([
            'queue:discovery' => ['throughput' => '2', 'runtime' => '150'],
        ]);
    }
}
