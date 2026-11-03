<?php

namespace LibreNMS\Tests\Unit\Data\Graphing\Builders;

use App\Data\Graphing\Builders\MultiSimplexSeparatedGraphBuilder;
use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\RrdFileTracker;
use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;
use Mockery;

class MultiSimplexSeparatedGraphBuilderTest extends TestCase
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

        LibrenmsConfig::shouldReceive('get')->with('graph_colours.mixed.0')->andReturn('FF0000');

        $builder = new MultiSimplexSeparatedGraphBuilder($series, $rrdFiles);

        $params = new GraphParameters(['from' => 12345, 'to' => 67890]);
        $definition = $builder->build($params);

        $this->assertContains('DEF:field10=/opt/librenms/rrd/device/test.rrd:field1:AVERAGE', $definition);
        $this->assertContains('AREA:field10#FF0000:Description1     ', $definition);
    }

    public function test_it_skips_missing_rrd_files(): void
    {
        $metric = new MetricIdentity(1, 'test-metric');
        $series = [
            'series1' => new DataSeries($metric, 'field1', 'Description1'),
        ];

        $rrdFiles = Mockery::mock(RrdFileTracker::class);
        $rrdFiles->shouldReceive('pathFor')->with($metric)->once()->andReturn(null);

        $builder = new MultiSimplexSeparatedGraphBuilder($series, $rrdFiles);

        $params = new GraphParameters(['from' => 12345, 'to' => 67890]);
        $definition = $builder->build($params);

        // Should not contain DEF or AREA for field10
        foreach ($definition as $line) {
            $this->assertStringNotContainsString('field10', $line);
        }
    }

    public function test_it_scales_with_a_divider_and_keeps_the_original_values_in_the_legend(): void
    {
        $metric = new MetricIdentity(1, 'test-metric');
        $series = [
            'a' => new DataSeries($metric, 'usage', 'A'),
            'b' => new DataSeries($metric, 'usage', 'B'),
        ];
        $rrdFiles = Mockery::mock(RrdFileTracker::class);
        $rrdFiles->shouldReceive('pathFor')->with($metric)->andReturn('host/test.rrd');

        $builder = (new MultiSimplexSeparatedGraphBuilder($series, $rrdFiles))
            ->unitText('Load %')
            ->divider(2)
            ->textOrig();

        $this->assertSame([
            "COMMENT:Load %                  Now     Min     Max     Avg\l",
            'DEF:usage0=host/test.rrd:usage:AVERAGE',
            'DEF:usage0min=host/test.rrd:usage:MIN',
            'DEF:usage0max=host/test.rrd:usage:MAX',
            'CDEF:usage_cdef0=usage0,2,/',
            'CDEF:usage_cdef0min=usage0min,2,/',
            'CDEF:usage_cdef0max=usage0max,2,/',
            'AREA:usage_cdef0#FF0000:A                ',
            'GPRINT:usage0:LAST:%5.2lf%s',
            'GPRINT:usage0min:MIN:%5.2lf%s',
            'GPRINT:usage0max:MAX:%5.2lf%s',
            'GPRINT:usage0:AVERAGE:%5.2lf%s\n',
            'COMMENT:\n',
            'DEF:usage1=host/test.rrd:usage:AVERAGE',
            'DEF:usage1min=host/test.rrd:usage:MIN',
            'DEF:usage1max=host/test.rrd:usage:MAX',
            'CDEF:usage_cdef1=usage1,2,/',
            'CDEF:usage_cdef1min=usage1min,2,/',
            'CDEF:usage_cdef1max=usage1max,2,/',
            'AREA:usage_cdef1#FF0000:B                :STACK',
            'GPRINT:usage1:LAST:%5.2lf%s',
            'GPRINT:usage1min:MIN:%5.2lf%s',
            'GPRINT:usage1max:MAX:%5.2lf%s',
            'GPRINT:usage1:AVERAGE:%5.2lf%s\n',
            'COMMENT:\n',
        ], $builder->build(new GraphParameters(['from' => 100, 'to' => 200])));
    }

    public function test_it_draws_the_previous_period_and_totals_with_a_multiplier(): void
    {
        $metric = new MetricIdentity(1, 'test-metric');
        $series = [
            'a' => new DataSeries($metric, 'bytes', 'A'),
            'b' => new DataSeries($metric, 'bytes', 'B'),
        ];
        $rrdFiles = Mockery::mock(RrdFileTracker::class);
        $rrdFiles->shouldReceive('pathFor')->with($metric)->andReturn('host/test.rrd');

        $builder = (new MultiSimplexSeparatedGraphBuilder($series, $rrdFiles))
            ->unitText('Bits')
            ->multiplier(8)
            ->showTotal(units: 'B')
            ->setSeriesStyle('b', new SeriesStyle(color: 'AA0000'));

        $this->assertSame([
            "COMMENT:Bits                Now     Min     Max     Avg\l",
            'DEF:bytes0=host/test.rrd:bytes:AVERAGE',
            'DEF:bytes0min=host/test.rrd:bytes:MIN',
            'DEF:bytes0max=host/test.rrd:bytes:MAX',
            'DEF:0X=host/test.rrd:bytes:AVERAGE:start=0:end=100',
            'SHIFT:0X:100',
            'VDEF:totbytes0=bytes0,TOTAL',
            'CDEF:bytes_cdef0=bytes0,8,*',
            'CDEF:bytes_cdef0min=bytes0min,8,*',
            'CDEF:bytes_cdef0max=bytes0max,8,*',
            'AREA:bytes_cdef0#FF0000:A            ',
            'GPRINT:bytes_cdef0:LAST:%5.2lf%s',
            'GPRINT:bytes_cdef0min:MIN:%5.2lf%s',
            'GPRINT:bytes_cdef0max:MAX:%5.2lf%s',
            'GPRINT:bytes_cdef0:AVERAGE:%5.2lf%s\n',
            'GPRINT:totbytes0:%6.2lf%sB',
            'COMMENT:\n',
            'DEF:bytes1=host/test.rrd:bytes:AVERAGE',
            'DEF:bytes1min=host/test.rrd:bytes:MIN',
            'DEF:bytes1max=host/test.rrd:bytes:MAX',
            'DEF:1X=host/test.rrd:bytes:AVERAGE:start=0:end=100',
            'SHIFT:1X:100',
            'VDEF:totbytes1=bytes1,TOTAL',
            'CDEF:bytes_cdef1=bytes1,8,*',
            'CDEF:bytes_cdef1min=bytes1min,8,*',
            'CDEF:bytes_cdef1max=bytes1max,8,*',
            'AREA:bytes_cdef1#AA0000:B            :STACK',
            'GPRINT:bytes_cdef1:LAST:%5.2lf%s',
            'GPRINT:bytes_cdef1min:MIN:%5.2lf%s',
            'GPRINT:bytes_cdef1max:MAX:%5.2lf%s',
            'GPRINT:bytes_cdef1:AVERAGE:%5.2lf%s\n',
            'GPRINT:totbytes1:%6.2lf%sB',
            'COMMENT:\n',
            'CDEF:X=0X,UN,0,0X,IF,1X,UN,0,1X,IF,+,8,*',
            'AREA:X#99999999:',
            'LINE1.25:X#666666:',
        ], $builder->build(new GraphParameters(['from' => 100, 'to' => 200, 'previous' => 'yes'])));
    }
}
