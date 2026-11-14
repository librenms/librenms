<?php

namespace LibreNMS\Tests\Unit\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\TimeSeries\MetricIdentity;
use App\Graphs\Rrd\RrdFileResolver;
use App\Graphs\Rrd\RrdSeriesReader;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\Tests\TestCase;
use Mockery;

class RrdSeriesReaderTest extends TestCase
{
    private MetricIdentity $metric;
    private MetricIdentity $missing;
    private RrdFileResolver&Mockery\MockInterface $rrdFiles;
    private RrdBackendInterface&Mockery\MockInterface $backend;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metric = new MetricIdentity('traffic', ['hostname' => 'localhost']);
        $this->missing = new MetricIdentity('missing', ['hostname' => 'localhost']);
        $this->rrdFiles = Mockery::mock(RrdFileResolver::class);
        $this->rrdFiles->shouldReceive('pathFor')->with($this->metric)->andReturn('localhost/traffic.rrd');
        $this->rrdFiles->shouldReceive('pathFor')->with($this->missing)->andReturn(null);
        $this->backend = Mockery::mock(RrdBackendInterface::class);
    }

    public function test_it_exports_the_average_min_and_max_of_each_series(): void
    {
        $this->backend->shouldReceive('xport')->once()->with([
            '--start', '1000', '--end', '1900', '--maxrows', '500',
            'DEF:ds0avg=localhost/traffic.rrd:in:AVERAGE', 'XPORT:ds0avg:ds0avg',
            'DEF:ds0min=localhost/traffic.rrd:in:MIN', 'XPORT:ds0min:ds0min',
            'DEF:ds0max=localhost/traffic.rrd:in:MAX', 'XPORT:ds0max:ds0max',
            'DEF:ds1avg=localhost/traffic.rrd:out:AVERAGE', 'XPORT:ds1avg:ds1avg',
            'DEF:ds1min=localhost/traffic.rrd:out:MIN', 'XPORT:ds1min:ds1min',
            'DEF:ds1max=localhost/traffic.rrd:out:MAX', 'XPORT:ds1max:ds1max',
        ])->andReturn(array_map(fn (array $values) => array_combine([1200, 1500, 1800], $values), [
            'ds0avg' => [2.0, null, 4.0], 'ds0min' => [1.0, null, 3.0], 'ds0max' => [3.0, null, 5.0],
            'ds1avg' => [20.0, null, 40.0], 'ds1min' => [10.0, null, 30.0], 'ds1max' => [30.0, null, 50.0],
        ]));

        $data = $this->reader()->read([
            'in' => new DataSeries($this->metric, 'in', 'In'),
            'gone' => new DataSeries($this->missing, 'in', 'Gone'),
            'out' => new DataSeries($this->metric, 'out', 'Out'),
        ], 1000, 1900, 500);

        $this->assertSame([1200, 1500, 1800], $data->timestamps);
        $this->assertSame(['in', 'out'], array_keys($data->series), 'series without an rrd file are left out');
        $this->assertSame([2.0, null, 4.0], $data->series['in']['avg']);
        $this->assertSame([1.0, null, 3.0], $data->series['in']['min']);
        $this->assertSame([3.0, null, 5.0], $data->series['in']['max']);
        $this->assertSame([20.0, null, 40.0], $data->series['out']['avg']);
    }

    public function test_values_are_scaled_to_the_value_of_the_series(): void
    {
        $this->backend->shouldReceive('xport')->andReturn([
            'ds0avg' => [1200 => 2.0, 1500 => null], 'ds0min' => [1200 => 1.0, 1500 => null], 'ds0max' => [1200 => 3.0, 1500 => null],
        ]);

        $data = $this->reader()->read(['bits' => new DataSeries($this->metric, 'bytes', 'Traffic', 'bps', 8)], 1000, 1900, 500);

        $this->assertSame([16.0, null], $data->series['bits']['avg']);
        $this->assertSame([8.0, null], $data->series['bits']['min']);
        $this->assertSame([24.0, null], $data->series['bits']['max']);
    }

    public function test_rrdtool_is_asked_for_at_least_its_minimum_of_rows(): void
    {
        $this->backend->shouldReceive('xport')->once()
            ->with(Mockery::on(fn (array $options) => array_slice($options, 4, 2) === ['--maxrows', '10']))
            ->andReturn([]);

        $data = $this->reader()->read(['in' => new DataSeries($this->metric, 'in', 'In')], 1000, 1900, 1);

        $this->assertSame([], $data->timestamps);
    }

    public function test_nothing_is_read_when_no_series_has_an_rrd_file(): void
    {
        $this->backend->shouldNotReceive('xport');

        $data = $this->reader()->read(['gone' => new DataSeries($this->missing, 'in', 'Gone')], 1000, 1900, 500);

        $this->assertSame([], $data->series);
        $this->assertSame([], $data->timestamps);
    }

    private function reader(): RrdSeriesReader
    {
        return new RrdSeriesReader($this->rrdFiles, $this->backend);
    }
}
