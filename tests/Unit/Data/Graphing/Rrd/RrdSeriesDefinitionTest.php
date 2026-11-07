<?php

namespace LibreNMS\Tests\Unit\Data\Graphing\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\Rrd\RrdSeriesDefinition;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\Rrd\RrdFileResolver;
use App\Facades\LibrenmsConfig;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;
use Mockery;

class RrdSeriesDefinitionTest extends TestCase
{
    private MetricIdentity $metric;
    private RrdFileResolver&Mockery\MockInterface $rrdFiles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metric = new MetricIdentity(1, 'test-metric');
        $this->rrdFiles = Mockery::mock(RrdFileResolver::class);
        $this->rrdFiles->shouldReceive('pathFor')->with($this->metric)->andReturn('host/test.rrd')->byDefault();
        LibrenmsConfig::shouldReceive('get')->byDefault()->andReturn(null);
        LibrenmsConfig::shouldReceive('has')->byDefault()->andReturn(true);
        LibrenmsConfig::shouldReceive('get')->with(Mockery::pattern('/^graph_colours\./'))->byDefault()->andReturn('FF0000');
    }

    public function test_it_draws_lines_with_fill_and_invert(): void
    {
        $this->assertSame([
            "COMMENT:Traffic                    Now        Min        Max        Avg\l",
            'DEF:ds0=host/test.rrd:in:AVERAGE',
            'DEF:ds0min=host/test.rrd:in:MIN',
            'DEF:ds0max=host/test.rrd:in:MAX',
            'DEF:ds1=host/test.rrd:out:AVERAGE',
            'DEF:ds1min=host/test.rrd:out:MIN',
            'DEF:ds1max=host/test.rrd:out:MAX',
            'CDEF:ds1drawn=ds1,-1,*',
            'LINE1.25:ds0#FF0000:In               ',
            'AREA:ds0#FF000020',
            'GPRINT:ds0:LAST:%5.2lf%sbps',
            'GPRINT:ds0min:MIN:%5.2lf%sbps',
            'GPRINT:ds0max:MAX:%5.2lf%sbps',
            'GPRINT:ds0:AVERAGE:%5.2lf%sbps\n',
            'LINE1.25:ds1drawn#AA0000:Out              ',
            'AREA:ds1drawn#BB0000',
            'GPRINT:ds1:LAST:%5.2lf%sbps',
            'GPRINT:ds1min:MIN:%5.2lf%sbps',
            'GPRINT:ds1max:MAX:%5.2lf%sbps',
            'GPRINT:ds1:AVERAGE:%5.2lf%sbps\n',
            'HRULE:0#555555',
        ], $this->build([
            'in' => new DataSeries($this->metric, 'in', 'In', 'bps', style: new SeriesStyle(fill: true)),
            'out' => new DataSeries($this->metric, 'out', 'Out', 'bps', style: new SeriesStyle(color: 'AA0000', fill: true, fillColor: 'BB0000', invert: true)),
        ], new GraphOptions(legendTitle: 'Traffic')));
    }

    public function test_draw_scale_only_changes_the_drawing(): void
    {
        $this->assertSame([
            "COMMENT:Load %                  Now     Min     Max     Avg\l",
            'DEF:ds0=host/test.rrd:usage:AVERAGE',
            'DEF:ds0min=host/test.rrd:usage:MIN',
            'DEF:ds0max=host/test.rrd:usage:MAX',
            'CDEF:ds0drawn=ds0,0.5,*',
            'DEF:ds1=host/test.rrd:usage:AVERAGE',
            'DEF:ds1min=host/test.rrd:usage:MIN',
            'DEF:ds1max=host/test.rrd:usage:MAX',
            'CDEF:ds1drawn=ds1,0.5,*',
            'AREA:ds0drawn#FF0000:A                ',
            'GPRINT:ds0:LAST:%5.2lf%s',
            'GPRINT:ds0min:MIN:%5.2lf%s',
            'GPRINT:ds0max:MAX:%5.2lf%s',
            'GPRINT:ds0:AVERAGE:%5.2lf%s\n',
            'AREA:ds1drawn#FF0000:B                :STACK',
            'GPRINT:ds1:LAST:%5.2lf%s',
            'GPRINT:ds1min:MIN:%5.2lf%s',
            'GPRINT:ds1max:MAX:%5.2lf%s',
            'GPRINT:ds1:AVERAGE:%5.2lf%s\n',
        ], $this->build([
            'a' => new DataSeries($this->metric, 'usage', 'A', style: new SeriesStyle(stacked: true, drawScale: 0.5)),
            'b' => new DataSeries($this->metric, 'usage', 'B', style: new SeriesStyle(stacked: true, drawScale: 0.5)),
        ], new GraphOptions(legendTitle: 'Load %')));
    }

    public function test_series_scale_changes_the_value_everywhere(): void
    {
        $this->assertSame([
            "COMMENT:                        Now     Min     Max     Avg\l",
            'DEF:ds0raw=host/test.rrd:bytes:AVERAGE',
            'CDEF:ds0=ds0raw,8,*',
            'DEF:ds0rawmin=host/test.rrd:bytes:MIN',
            'CDEF:ds0min=ds0rawmin,8,*',
            'DEF:ds0rawmax=host/test.rrd:bytes:MAX',
            'CDEF:ds0max=ds0rawmax,8,*',
            'LINE1.25:ds0#FF0000:Bits             ',
            'GPRINT:ds0:LAST:%5.2lf%s',
            'GPRINT:ds0min:MIN:%5.2lf%s',
            'GPRINT:ds0max:MAX:%5.2lf%s',
            'GPRINT:ds0:AVERAGE:%5.2lf%s\n',
        ], $this->build(['a' => new DataSeries($this->metric, 'bytes', 'Bits', scale: 8)]));
    }

    public function test_it_adds_the_previous_period_of_stacked_series(): void
    {
        $definition = $this->build([
            'a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(stacked: true)),
            'b' => new DataSeries($this->metric, 'b', 'B', scale: 8, style: new SeriesStyle(stacked: true, drawScale: 0.5)),
        ], params: ['from' => 100, 'to' => 200, 'previous' => 'yes']);

        $this->assertContains('DEF:ds0prev=host/test.rrd:a:AVERAGE:start=0:end=100', $definition);
        $this->assertContains('SHIFT:ds1prev:100', $definition);
        $this->assertContains('CDEF:previous=ds0prev,UN,0,ds0prev,IF,ds1prev,UN,0,ds1prev,IF,4,*,+', $definition);
        $this->assertContains('AREA:previous#99999999:', $definition);
    }

    public function test_lines_do_not_draw_the_previous_period(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A')], params: ['from' => 100, 'to' => 200, 'previous' => 'yes']);

        $this->assertEmpty(preg_grep('/previous|prev/', $definition));
    }

    public function test_it_uses_the_palette_of_the_options(): void
    {
        LibrenmsConfig::shouldReceive('get')->with('graph_colours.oranges.0')->andReturn('EE7700');

        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A')], new GraphOptions(palette: 'oranges'));

        $this->assertContains('LINE1.25:ds0#EE7700:A                ', $definition);
    }

    public function test_it_escapes_units_in_value_formats_and_widens_the_columns(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', 'a%:b')]);

        $this->assertContains('GPRINT:ds0:LAST:%5.2lf%sa%%\:b', $definition);
        $this->assertSame("COMMENT:                            Now         Min         Max         Avg\l", $definition[0]);
    }

    public function test_it_skips_series_without_an_rrd_file(): void
    {
        $missing = new MetricIdentity(1, 'missing');
        $this->rrdFiles->shouldReceive('pathFor')->with($missing)->andReturn(null);

        $definition = $this->build([
            'gone' => new DataSeries($missing, 'a', 'Gone'),
            'here' => new DataSeries($this->metric, 'b', 'Here'),
        ]);

        $this->assertContains('DEF:ds0=host/test.rrd:b:AVERAGE', $definition);
        $this->assertEmpty(preg_grep('/Gone/', $definition));
    }

    public function test_it_reports_no_data_when_no_series_has_an_rrd_file(): void
    {
        $this->rrdFiles->shouldReceive('pathFor')->with($this->metric)->andReturn(null);

        $this->expectException(NoGraphData::class);
        $this->expectExceptionMessage('No Data files');

        $this->build(['a' => new DataSeries($this->metric, 'a', 'A')]);
    }

    /**
     * @param  array<string, DataSeries>  $series
     * @param  array<string, mixed>  $params
     * @return array<int, string>
     */
    private function build(array $series, GraphOptions $options = new GraphOptions, array $params = ['from' => 100, 'to' => 200]): array
    {
        return new RrdSeriesDefinition($series, $options, $this->rrdFiles)->build(new GraphParameters($params));
    }
}
