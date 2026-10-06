<?php

namespace LibreNMS\Tests\Unit\TimeSeries\Rrd;

use App\Data\TimeSeries\Contracts\RrdPathResolver;
use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\RrdFileValidator;
use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Tests\TestCase;
use Mockery;

class RrdFileValidatorTest extends TestCase
{
    private RrdFileValidator $validator;
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = Mockery::mock(RrdPathResolver::class);
        $this->validator = new RrdFileValidator($this->resolver);
        LibrenmsConfig::shouldReceive('get')->byDefault()->andReturn(null);
        LibrenmsConfig::shouldReceive('get')->with('rrd_dir')->byDefault()->andReturn('/opt/librenms/rrd');
        LibrenmsConfig::shouldReceive('get')->with('rrdcached')->byDefault()->andReturn(false);
    }

    private function pathMatcher(string $relative): Mockery\Matcher\Closure
    {
        return Mockery::on(fn ($path) => $path instanceof RrdPath && $path->relativePath() === $relative);
    }

    public function test_it_validates_metric_identity(): void
    {
        $metric = new MetricIdentity('mempool', ['device_id' => 1]);
        $this->resolver->shouldReceive('resolve')->with($metric)->once()->andReturn(RrdPath::make('localhost', 'mempool-1.rrd'));
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/mempool-1.rrd'))->once()->andReturn(true);

        $path = $this->validator->validate($metric);

        $this->assertEquals('/opt/librenms/rrd/localhost/mempool-1.rrd', $path);
        $this->assertTrue($this->validator->hasAttempted());
        $this->assertTrue($this->validator->hasValidFiles());
    }

    public function test_it_caches_validation_results(): void
    {
        $metric = new MetricIdentity('mempool', ['device_id' => 1]);
        $this->resolver->shouldReceive('resolve')->with($metric)->twice()->andReturn(RrdPath::make('localhost', 'mempool-1.rrd'));
        Rrd::shouldReceive('checkRrdExists')->once()->andReturn(true);

        $this->validator->validate($metric);
        $path = $this->validator->validate($metric);

        $this->assertEquals('/opt/librenms/rrd/localhost/mempool-1.rrd', $path);
    }

    public function test_it_returns_null_if_file_does_not_exist(): void
    {
        $metric = new MetricIdentity('mempool', ['device_id' => 1]);
        $this->resolver->shouldReceive('resolve')->with($metric)->once()->andReturn(RrdPath::make('localhost', 'nonexistent.rrd'));
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('localhost/nonexistent.rrd'))->once()->andReturn(false);

        $path = $this->validator->validate($metric);

        $this->assertNull($path);
        $this->assertTrue($this->validator->hasAttempted());
        $this->assertFalse($this->validator->hasValidFiles());
    }

    public function test_it_validates_rrd_path(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with($this->pathMatcher('custom/file.rrd'))->once()->andReturn(true);

        $path = $this->validator->validate(RrdPath::make('custom', 'file.rrd'));

        $this->assertEquals('/opt/librenms/rrd/custom/file.rrd', $path);
    }

    public function test_it_returns_relative_path_with_rrdcached(): void
    {
        LibrenmsConfig::shouldReceive('get')->with('rrdcached')->andReturn('localhost:42217');
        Rrd::shouldReceive('checkRrdExists')->once()->andReturn(true);

        $path = $this->validator->validate(RrdPath::make('custom', 'file.rrd'));

        $this->assertEquals('custom/file.rrd', $path);
    }
}
