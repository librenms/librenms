<?php

namespace LibreNMS\Tests\Unit\TimeSeries\Rrd;

use App\Facades\LibrenmsConfig;
use App\Facades\Rrd;
use App\Data\TimeSeries\Contracts\RrdPathResolver;
use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\RrdFileValidator;
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
        LibrenmsConfig::shouldReceive('get')->with('rrdcached', false)->byDefault()->andReturn(false);
    }

    public function test_it_validates_metric_identity(): void
    {
        $metric = new MetricIdentity('mempool', ['device_id' => 1]);
        $this->resolver->shouldReceive('resolve')->with($metric)->once()->andReturn('localhost/mempool-1.rrd');
        Rrd::shouldReceive('checkRrdExists')->with('/opt/librenms/rrd/localhost/mempool-1.rrd')->once()->andReturn(true);

        $path = $this->validator->validate($metric);

        $this->assertEquals('/opt/librenms/rrd/localhost/mempool-1.rrd', $path);
    }

    public function test_it_caches_validation_results(): void
    {
        $metric = new MetricIdentity('mempool', ['device_id' => 1]);
        // Resolve is called every time before the cache check currently
        $this->resolver->shouldReceive('resolve')->with($metric)->twice()->andReturn('localhost/mempool-1.rrd');
        Rrd::shouldReceive('checkRrdExists')->with('/opt/librenms/rrd/localhost/mempool-1.rrd')->once()->andReturn(true);

        // Call twice
        $this->validator->validate($metric);
        $path = $this->validator->validate($metric);

        $this->assertEquals('/opt/librenms/rrd/localhost/mempool-1.rrd', $path);
    }

    public function test_it_returns_null_if_file_does_not_exist(): void
    {
        $metric = new MetricIdentity('mempool', ['device_id' => 1]);
        $this->resolver->shouldReceive('resolve')->with($metric)->once()->andReturn('localhost/nonexistent.rrd');
        Rrd::shouldReceive('checkRrdExists')->with('/opt/librenms/rrd/localhost/nonexistent.rrd')->once()->andReturn(false);

        $path = $this->validator->validate($metric);

        $this->assertNull($path);
    }

    public function test_it_validates_raw_filename(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with('/opt/librenms/rrd/custom/file.rrd')->once()->andReturn(true);

        $path = $this->validator->validate(null, ['filename' => 'custom/file.rrd']);

        $this->assertEquals('/opt/librenms/rrd/custom/file.rrd', $path);
    }

    public function test_it_handles_absolute_paths(): void
    {
        Rrd::shouldReceive('checkRrdExists')->with('/tmp/test.rrd')->once()->andReturn(true);

        $path = $this->validator->validate(null, ['filename' => '/tmp/test.rrd']);

        $this->assertEquals('/tmp/test.rrd', $path);
    }
}
