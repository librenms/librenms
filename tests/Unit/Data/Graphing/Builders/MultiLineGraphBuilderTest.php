<?php

namespace LibreNMS\Tests\Unit\Data\Graphing\Builders;

use App\Data\Graphing\Builders\MultiLineGraphBuilder;
use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\RrdFileTracker;
use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;
use Mockery;

class MultiLineGraphBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::shouldReceive('get')->byDefault()->andReturn(null);
        LibrenmsConfig::shouldReceive('get')->with('mono_font')->byDefault()->andReturn('DejaVuSansMono');
        LibrenmsConfig::shouldReceive('has')->byDefault()->andReturn(true);
        LibrenmsConfig::shouldReceive('get')->with(Mockery::pattern('/^graph_colours\./'))->byDefault()->andReturn('FF0000');
    }

    public function test_it_builds_rrd_command_from_data_series(): void
    {
        $metric = new MetricIdentity(1, 'test-metric');
        $series = [
            'series1' => new DataSeries($metric, 'field1', 'Description1'),
        ];

        $rrdFiles = Mockery::mock(RrdFileTracker::class);
        $rrdFiles->shouldReceive('pathFor')->with($metric)->once()->andReturn('/opt/librenms/rrd/device/test.rrd');

        LibrenmsConfig::shouldReceive('get')->with('webui.graph_stacked')->andReturn(false);
        LibrenmsConfig::shouldReceive('get')->with('graph_colours.mixed.0')->andReturn('FF0000');

        $builder = new MultiLineGraphBuilder($series, $rrdFiles);

        $params = new GraphParameters(['from' => 12345, 'to' => 67890]);
        $definition = $builder->build($params);

        $this->assertContains('DEF:ds0=/opt/librenms/rrd/device/test.rrd:field1:AVERAGE', $definition);
        $this->assertContains('LINE1.25:ds0#FF0000:Description1   ', $definition);
    }

    public function test_it_skips_missing_rrd_files(): void
    {
        $metric = new MetricIdentity(1, 'test-metric');
        $series = [
            'series1' => new DataSeries($metric, 'field1', 'Description 1'),
        ];

        $rrdFiles = Mockery::mock(RrdFileTracker::class);
        $rrdFiles->shouldReceive('pathFor')->with($metric)->once()->andReturn(null);

        $builder = new MultiLineGraphBuilder($series, $rrdFiles);

        $params = new GraphParameters(['from' => 12345, 'to' => 67890]);
        $definition = $builder->build($params);

        // Should not contain DEF or LINE for series1
        foreach ($definition as $line) {
            $this->assertStringNotContainsString('series1', $line);
        }
    }

    public function test_it_builds_the_exact_definition(): void
    {
        $metric = new MetricIdentity(1, 'test-metric');
        $series = [
            'in' => new DataSeries($metric, 'in', 'In'),
            'out' => new DataSeries($metric, 'out', 'Out'),
        ];
        $rrdFiles = Mockery::mock(RrdFileTracker::class);
        $rrdFiles->shouldReceive('pathFor')->with($metric)->andReturn('host/test.rrd');

        $builder = (new MultiLineGraphBuilder($series, $rrdFiles))
            ->units('bps', 'Traffic')
            ->setSeriesStyle('in', new SeriesStyle(area: true))
            ->setSeriesStyle('out', new SeriesStyle(color: 'AA0000', area: true, areaColor: 'BB0000', invert: true));

        $this->assertSame([
            "COMMENT:Traffic                  Now        Min        Max        Avg\l",
            'DEF:ds0=host/test.rrd:in:AVERAGE',
            'DEF:ds0min=host/test.rrd:in:MIN',
            'DEF:ds0max=host/test.rrd:in:MAX',
            'DEF:ds1=host/test.rrd:out:AVERAGE',
            'DEF:ds1min=host/test.rrd:out:MIN',
            'DEF:ds1max=host/test.rrd:out:MAX',
            'CDEF:ds1i=ds1,-1,*',
            'LINE1.25:ds0#FF0000:In             ',
            'AREA:ds0#FF000020',
            'GPRINT:ds0:LAST:%5.2lf%sbps',
            'GPRINT:ds0min:MIN:%5.2lf%sbps',
            'GPRINT:ds0max:MAX:%5.2lf%sbps',
            'GPRINT:ds0:AVERAGE:%5.2lf%sbps\n',
            'LINE1.25:ds1i#AA0000:Out            ',
            'AREA:ds1i#BB0000',
            'GPRINT:ds1:LAST:%5.2lf%sbps',
            'GPRINT:ds1min:MIN:%5.2lf%sbps',
            'GPRINT:ds1max:MAX:%5.2lf%sbps',
            'GPRINT:ds1:AVERAGE:%5.2lf%sbps\n',
            'HRULE:0#555555',
        ], $builder->build(new GraphParameters(['from' => 100, 'to' => 200])));
    }
}
