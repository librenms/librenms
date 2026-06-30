<?php

namespace LibreNMS\Tests\Unit\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\RrdFileResolver;
use App\Facades\DeviceCache;
use App\Facades\Rrd;
use App\Models\Device;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\TestCase;
use Mockery;

class RrdFileResolverTest extends TestCase
{
    private RrdFileResolver $rrdFiles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rrdFiles = new RrdFileResolver;
        Rrd::partialMock();
        DeviceCache::shouldReceive('get')->with(1)->byDefault()->andReturn(new Device(['hostname' => 'localhost']));
    }

    private function pathMatcher(string $relative): Mockery\Matcher\Closure
    {
        return Mockery::on(fn ($path) => $path instanceof RrdPath && $path->relativePath() === $relative);
    }

    public function test_it_resolves_the_rrd_name(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/mempool-1.rrd'))->once()->andReturn(true);

        $this->assertSame('localhost/mempool-1.rrd', $this->rrdFiles->pathFor(new MetricIdentity(1, 'mempools', ['mempool_index' => 1], ['mempool', 1])));
    }

    public function test_it_names_files_after_the_measurement_without_rrd_name(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/netstats-ip.rrd'))->once()->andReturn(true);

        $this->assertSame('localhost/netstats-ip.rrd', $this->rrdFiles->pathFor(new MetricIdentity(1, 'netstats-ip', ['ignored' => 'tag'])));
    }

    public function test_it_checks_each_file_once(): void
    {
        $metric = new MetricIdentity(1, 'netstats-ip');
        Rrd::shouldReceive('checkRrdExists')->once()->andReturn(false);

        $this->assertNull($this->rrdFiles->pathFor($metric));
        $this->assertNull($this->rrdFiles->pathFor($metric));
    }

    public function test_it_returns_null_for_unknown_devices(): void
    {
        DeviceCache::shouldReceive('get')->with(2)->andReturn(new Device);
        Rrd::shouldReceive('checkRrdExists')->never();

        $this->assertNull($this->rrdFiles->pathFor(new MetricIdentity(2, 'netstats-ip')));
    }
}
