<?php

namespace LibreNMS\Tests\Unit\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\RrdFileTracker;
use App\Facades\DeviceCache;
use App\Facades\Rrd;
use App\Models\Device;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\TestCase;
use Mockery;

class RrdFileTrackerTest extends TestCase
{
    private RrdFileTracker $rrdFiles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rrdFiles = new RrdFileTracker;
        Rrd::partialMock();
        DeviceCache::shouldReceive('get')->with(1)->byDefault()->andReturn(new Device(['hostname' => 'localhost']));
    }

    private function pathMatcher(string $relative): Mockery\Matcher\Closure
    {
        return Mockery::on(fn ($path) => $path instanceof RrdPath && $path->relativePath() === $relative);
    }

    public function test_it_validates_metric_identity(): void
    {
        $metric = new MetricIdentity(1, 'mempools', ['mempool_index' => 1], ['mempool', 1]);
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/mempool-1.rrd'))->once()->andReturn(true);

        $path = $this->rrdFiles->pathFor($metric);

        $this->assertEquals('localhost/mempool-1.rrd', $path);
        $this->assertFalse($this->rrdFiles->foundNoFiles());
    }

    public function test_it_has_not_found_no_files_before_checking(): void
    {
        $this->assertFalse($this->rrdFiles->foundNoFiles());
    }

    public function test_it_caches_validation_results(): void
    {
        $metric = new MetricIdentity(1, 'mempools', ['mempool_index' => 1], ['mempool', 1]);
        Rrd::shouldReceive('checkRrdExists')->once()->andReturn(true);

        $this->rrdFiles->pathFor($metric);
        $path = $this->rrdFiles->pathFor($metric);

        $this->assertEquals('localhost/mempool-1.rrd', $path);
    }

    public function test_it_returns_null_if_file_does_not_exist(): void
    {
        $metric = new MetricIdentity(1, 'nonexistent');
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/nonexistent.rrd'))->once()->andReturn(false);

        $path = $this->rrdFiles->pathFor($metric);

        $this->assertNull($path);
        $this->assertTrue($this->rrdFiles->foundNoFiles());
    }

    public function test_it_names_files_after_the_measurement_without_rrd_name(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/netstats-ip.rrd'))->once()->andReturn(true);

        $path = $this->rrdFiles->pathFor(new MetricIdentity(1, 'netstats-ip', ['ignored' => 'tag']));

        $this->assertEquals('localhost/netstats-ip.rrd', $path);
    }

    public function test_it_returns_null_for_unknown_device(): void
    {
        DeviceCache::shouldReceive('get')->with(2)->andReturn(new Device);
        Rrd::shouldReceive('checkRrdExists')->never();

        $this->assertNull($this->rrdFiles->pathFor(new MetricIdentity(2, 'netstats-ip')));
        $this->assertTrue($this->rrdFiles->foundNoFiles());
    }

    public function test_it_validates_rrd_path(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('custom/file.rrd'))->once()->andReturn(true);

        $path = $this->rrdFiles->pathFor(RrdPath::make('custom', 'file.rrd'));

        $this->assertEquals('custom/file.rrd', $path);
    }
}
