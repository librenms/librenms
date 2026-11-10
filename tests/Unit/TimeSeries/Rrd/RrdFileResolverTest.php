<?php

namespace LibreNMS\Tests\Unit\TimeSeries\Rrd;

use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\RrdFileResolver;
use App\Facades\Rrd;
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
    }

    private function pathMatcher(string $relative): Mockery\Matcher\Closure
    {
        return Mockery::on(fn ($path) => $path instanceof RrdPath && $path->relativePath() === $relative);
    }

    private function processor(): MetricIdentity
    {
        return new MetricIdentity('processors', ['hostname' => 'localhost', 'type' => 'hr', 'index' => 1]);
    }

    public function test_it_names_files_after_the_measurement_in_the_hostname_directory(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/netstats-ip.rrd'))->once()->andReturn(true);

        $this->assertSame('localhost/netstats-ip.rrd', $this->rrdFiles->pathFor(new MetricIdentity('netstats-ip', ['hostname' => 'localhost'])));
    }

    public function test_it_names_files_after_the_measurement_and_other_tag_values(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/vm.cpu-qemu-100.rrd'))->once()->andReturn(true);

        $metric = new MetricIdentity('vm.cpu', ['type' => 'qemu', 'hostname' => 'localhost', 'id' => 100]);

        $this->assertSame('localhost/vm.cpu-qemu-100.rrd', $this->rrdFiles->pathFor($metric));
    }

    public function test_a_metric_without_a_hostname_is_kept_in_the_directory_of_its_first_tag(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('proxmox-abc/vm.cpu-qemu-100.rrd'))->once()->andReturn(true);

        $metric = new MetricIdentity('vm.cpu', ['cluster' => 'proxmox-abc', 'type' => 'qemu', 'id' => 100]);

        $this->assertSame('proxmox-abc/vm.cpu-qemu-100.rrd', $this->rrdFiles->pathFor($metric));
    }

    public function test_it_reads_a_file_that_was_not_renamed_under_its_legacy_name(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/processors-hr-1.rrd'))->once()->andReturn(false);
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/processor-hr-1.rrd'))->once()->andReturn(true);

        $this->assertSame('localhost/processor-hr-1.rrd', $this->rrdFiles->pathFor($this->processor()));
    }

    public function test_it_prefers_the_current_name_over_the_legacy_name(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/processors-hr-1.rrd'))->once()->andReturn(true);

        $this->assertSame('localhost/processors-hr-1.rrd', $this->rrdFiles->pathFor($this->processor()));
    }

    public function test_it_returns_null_when_neither_file_exists(): void
    {
        Rrd::shouldReceive('checkRrdExists')->twice()->andReturn(false);

        $this->assertNull($this->rrdFiles->pathFor($this->processor()));
    }

    public function test_it_checks_each_file_once(): void
    {
        $metric = new MetricIdentity('netstats-ip', ['hostname' => 'localhost']);
        Rrd::shouldReceive('checkRrdExists')->once()->andReturn(false);

        $this->assertNull($this->rrdFiles->pathFor($metric));
        $this->assertNull($this->rrdFiles->pathFor($metric));
    }

    public function test_it_returns_null_without_tags_to_locate_the_file(): void
    {
        Rrd::shouldReceive('checkRrdExists')->never();

        $this->assertNull($this->rrdFiles->pathFor(new MetricIdentity('netstats-ip')));
        $this->assertNull($this->rrdFiles->pathFor(new MetricIdentity('netstats-ip', ['hostname' => ''])));
    }
}
