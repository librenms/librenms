<?php

namespace LibreNMS\Tests\Unit\TimeSeries\Rrd;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\LegacyRrdPathResolver;
use LibreNMS\Tests\TestCase;

class LegacyRrdPathResolverTest extends TestCase
{
    private LegacyRrdPathResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new LegacyRrdPathResolver();
    }

    public function test_it_resolves_mempool_paths(): void
    {
        $device = new Device(['hostname' => 'localhost']);
        DeviceCache::shouldReceive('get')->with(1)->andReturn($device);

        $identity = new MetricIdentity('mempool', [
            'device_id' => 1,
            'mempool_type' => 'hrStorage',
            'mempool_class' => 'ram',
            'mempool_index' => 1,
        ]);

        $path = $this->resolver->resolve($identity);

        $this->assertEquals('localhost/mempool-hrStorage-ram-1.rrd', $path);
    }

    public function test_it_resolves_processor_paths(): void
    {
        $device = new Device(['hostname' => 'localhost']);
        DeviceCache::shouldReceive('get')->with(1)->andReturn($device);

        $identity = new MetricIdentity('processor', [
            'device_id' => 1,
            'processor_type' => 'hr',
            'processor_index' => 0,
        ]);

        $path = $this->resolver->resolve($identity);

        $this->assertEquals('localhost/processor-hr-0.rrd', $path);
    }

    public function test_it_resolves_netstats_dash_ip_paths(): void
    {
        $device = new Device(['hostname' => 'localhost']);
        DeviceCache::shouldReceive('get')->with(1)->andReturn($device);

        $identity = new MetricIdentity('netstats-ip', [
            'device_id' => 1,
        ]);

        $path = $this->resolver->resolve($identity);

        $this->assertEquals('localhost/netstats-ip.rrd', $path);
    }

    public function test_it_handles_hostname_with_brackets(): void
    {
        $device = new Device(['hostname' => '[::1]']);
        DeviceCache::shouldReceive('get')->with(1)->andReturn($device);

        $identity = new MetricIdentity('mempool', [
            'device_id' => 1,
            'mempool_type' => 'hrStorage',
            'mempool_class' => 'ram',
            'mempool_index' => 1,
        ]);

        $path = $this->resolver->resolve($identity);

        $this->assertEquals('__1/mempool-hrStorage-ram-1.rrd', $path);
    }
}
