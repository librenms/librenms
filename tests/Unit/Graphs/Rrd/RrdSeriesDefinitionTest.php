<?php

namespace LibreNMS\Tests\Unit\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\Formula;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\ThemeColor;
use App\Data\Graphing\Threshold;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\RrdFileResolver;
use App\Graphs\Rrd\RrdSeriesDefinition;
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
        $this->metric = new MetricIdentity('test-metric', ['hostname' => 'localhost']);
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
            'AREA:ds0#FF000020',
            'AREA:ds1drawn#BB0000',
            'LINE1.25:ds0#FF0000:In               ',
            'GPRINT:ds0:LAST:%5.2lf%sbps',
            'GPRINT:ds0min:MIN:%5.2lf%sbps',
            'GPRINT:ds0max:MAX:%5.2lf%sbps',
            'GPRINT:ds0:AVERAGE:%5.2lf%sbps\n',
            'LINE1.25:ds1drawn#AA0000:Out              ',
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

    public function test_max_fills_are_drawn_behind_the_fills(): void
    {
        $definition = $this->build([
            'in' => new DataSeries($this->metric, 'in', 'In', scale: 8, style: new SeriesStyle(color: '608720', fill: true, fillColor: '90B040', maxFillColor: 'D7FFC7')),
            'out' => new DataSeries($this->metric, 'out', 'Out', style: new SeriesStyle(color: '606090', maxFillColor: 'E0E0FF', invert: true)),
        ]);

        $this->assertContains('CDEF:ds1maxdrawn=ds1max,-1,*', $definition);
        $drawing = array_values(preg_grep('/^(AREA|LINE)/', $definition));
        $this->assertSame([
            'AREA:ds0max#D7FFC7',
            'AREA:ds1maxdrawn#E0E0FF',
            'AREA:ds0#90B040',
            'LINE1.25:ds0#608720:In               ',
            'LINE1.25:ds1drawn#606090:Out              ',
        ], $drawing);
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
            'DEF:ds0minraw=host/test.rrd:bytes:MIN',
            'CDEF:ds0min=ds0minraw,8,*',
            'DEF:ds0maxraw=host/test.rrd:bytes:MAX',
            'CDEF:ds0max=ds0maxraw,8,*',
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
        $this->assertContains('SHIFT:ds0prev:100', $definition);
        $this->assertContains('DEF:ds1prevraw=host/test.rrd:b:AVERAGE:start=0:end=100', $definition);
        $this->assertContains('SHIFT:ds1prevraw:100', $definition);
        $this->assertContains('CDEF:ds1prev=ds1prevraw,8,*', $definition);
        $this->assertContains('CDEF:previous=ds0prev,UN,0,ds0prev,IF,ds1prev,UN,0,ds1prev,IF,0.5,*,+', $definition);
        $this->assertContains('AREA:previous#99999999:', $definition);
    }

    public function test_formulas_calculate_from_the_averages_of_their_fields(): void
    {
        $definition = $this->build(['loss' => new DataSeries($this->metric, new Formula('xmt', 'rcv', '-', 'xmt', '/', 100, '*'), 'Loss', '%')]);

        $this->assertSame([
            'DEF:ds00f=host/test.rrd:xmt:AVERAGE',
            'DEF:ds01f=host/test.rrd:rcv:AVERAGE',
            'CDEF:ds0=ds00f,ds01f,-,ds00f,/,100,*',
            'DEF:ds0min0f=host/test.rrd:xmt:AVERAGE',
            'DEF:ds0min1f=host/test.rrd:rcv:AVERAGE',
            'CDEF:ds0min=ds0min0f,ds0min1f,-,ds0min0f,/,100,*',
        ], array_slice($definition, 1, 6));
        $this->assertContains('GPRINT:ds0min:MIN:%5.2lf%s%%', $definition);
    }

    public function test_offset_is_added_after_the_scale(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'sensor', 'A', scale: 1.8, offset: 32)]);

        $this->assertContains('CDEF:ds0=ds0raw,1.8,*,32,+', $definition);
        $this->assertContains('CDEF:ds0max=ds0maxraw,1.8,*,32,+', $definition);
    }

    public function test_range_is_drawn_between_separate_min_and_max_fields(): void
    {
        $definition = $this->build([
            'rtt' => new DataSeries($this->metric, 'avg', 'RTT', minField: 'min', maxField: 'max', style: new SeriesStyle(color: '000000', rangeColor: 'CCCCCC', invert: true)),
        ]);

        $this->assertContains('DEF:ds0=host/test.rrd:avg:AVERAGE', $definition);
        $this->assertContains('DEF:ds0min=host/test.rrd:min:MIN', $definition);
        $this->assertContains('DEF:ds0max=host/test.rrd:max:MAX', $definition);
        $this->assertContains('CDEF:ds0range=ds0maxdrawn,ds0mindrawn,-', $definition);
        $drawing = array_values(preg_grep('/^(AREA|LINE)/', $definition));
        $this->assertSame(['LINE:ds0mindrawn#00000000', 'AREA:ds0range#CCCCCC::STACK', 'LINE1.25:ds0drawn#000000:RTT              '], $drawing);
    }

    public function test_inverted_stacked_series_start_their_own_stack(): void
    {
        $definition = $this->build([
            'in_a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(stacked: true)),
            'in_b' => new DataSeries($this->metric, 'b', 'B', style: new SeriesStyle(stacked: true)),
            'out_a' => new DataSeries($this->metric, 'c', 'C', style: new SeriesStyle(stacked: true, invert: true)),
            'out_b' => new DataSeries($this->metric, 'd', 'D', style: new SeriesStyle(stacked: true, invert: true)),
        ]);

        $this->assertSame([
            'AREA:ds0#FF0000:A                ',
            'AREA:ds1#FF0000:B                :STACK',
            'AREA:ds2drawn#FF0000:C                ',
            'AREA:ds3drawn#FF0000:D                :STACK',
        ], array_values(preg_grep('/^AREA/', $definition)));
    }

    public function test_thresholds_are_dashed_lines(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A')], new GraphOptions(thresholds: [
            new Threshold(-2.5, 'low = -2.5 dBm', '00008B'),
            new Threshold(80, 'high: 80 °C', 'FF0000'),
        ]));

        $this->assertContains('LINE1.5:-2.5#00008B:low = -2.5 dBm:dashes', $definition);
        $this->assertContains('LINE1.5:80#FF0000:high\: 80 °C\l:dashes', $definition);
    }

    public function test_scale_margin_widens_the_axis_with_invisible_lines(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A')], new GraphOptions(scaleMargin: 0.01));

        $this->assertContains('CDEF:ds0above=ds0max,ds0max,ABS,0.01,*,+', $definition);
        $this->assertContains('CDEF:ds0below=ds0min,ds0min,ABS,0.01,*,-', $definition);
        $this->assertSame(['LINE:ds0above#00000000', 'LINE:ds0below#00000000', 'LINE1.25:ds0#FF0000:A                '], array_values(preg_grep('/^LINE/', $definition)));
    }

    public function test_units_exponent_zero_shows_legend_values_without_prefixes(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', 'ms')], new GraphOptions(unitsExponent: 0));

        $this->assertContains('GPRINT:ds0:LAST:%5.2lf ms', $definition);
    }

    public function test_theme_colors_use_the_variation_of_the_style_of_the_request(): void
    {
        $series = ['a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(color: new ThemeColor('272B30', 'F2F2F2'), rangeColor: new ThemeColor('D0D0D0', '3E444C')))];

        $light = $this->build($series, params: ['from' => 100, 'to' => 200, 'style' => 'light']);
        $this->assertContains('LINE1.25:ds0#272B30:A                ', $light);
        $this->assertContains('AREA:ds0range#D0D0D0::STACK', $light);

        $dark = $this->build($series, params: ['from' => 100, 'to' => 200, 'style' => 'dark']);
        $this->assertContains('LINE1.25:ds0#F2F2F2:A                ', $dark);
        $this->assertContains('AREA:ds0range#3E444C::STACK', $dark);
    }

    public function test_the_previous_period_of_inverted_stacks_is_drawn_below_the_axis(): void
    {
        $definition = $this->build([
            'in' => new DataSeries($this->metric, 'a', 'In', style: new SeriesStyle(stacked: true)),
            'out' => new DataSeries($this->metric, 'b', 'Out', style: new SeriesStyle(stacked: true, invert: true)),
        ], params: ['from' => 100, 'to' => 200, 'previous' => 'yes']);

        $this->assertContains('CDEF:previous=ds0prev,UN,0,ds0prev,IF', $definition);
        $this->assertContains('CDEF:previousdown=ds1prev,UN,0,ds1prev,IF,-1,*', $definition);
        $this->assertContains('AREA:previous#99999999:', $definition);
        $this->assertContains('AREA:previousdown#99999999:', $definition);
    }

    public function test_scale_margin_of_an_inverted_line_widens_both_ends_of_the_drawing(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(invert: true))], new GraphOptions(scaleMargin: 0.01));

        $this->assertContains('CDEF:ds0above=ds0mindrawn,ds0mindrawn,ABS,0.01,*,+', $definition);
        $this->assertContains('CDEF:ds0below=ds0maxdrawn,ds0maxdrawn,ABS,0.01,*,-', $definition);
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
        $missing = new MetricIdentity('missing', ['hostname' => 'localhost']);
        $this->rrdFiles->shouldReceive('pathFor')->with($missing)->andReturn(null);

        $definition = $this->build([
            'gone' => new DataSeries($missing, 'a', 'Gone'),
            'here' => new DataSeries($this->metric, 'b', 'Here'),
        ]);

        $this->assertContains('DEF:ds0=host/test.rrd:b:AVERAGE', $definition);
        $this->assertEmpty(preg_grep('/Gone/', $definition));
    }

    public function test_stack_average_divides_by_the_series_with_rrd_files(): void
    {
        $missing = new MetricIdentity('test-metric', ['hostname' => 'localhost', 'index' => 3]);
        $this->rrdFiles->shouldReceive('pathFor')->with($missing)->andReturn(null);
        $style = new SeriesStyle(stacked: true, stackAverage: true);

        $definition = $this->build([
            'a' => new DataSeries($this->metric, 'usage', 'A', style: $style),
            'b' => new DataSeries($this->metric, 'usage', 'B', style: $style),
            'c' => new DataSeries($missing, 'usage', 'C', style: $style),
        ]);

        $this->assertContains('CDEF:ds0drawn=ds0,0.5,*', $definition);
        $this->assertContains('CDEF:ds1drawn=ds1,0.5,*', $definition);
        $this->assertEmpty(preg_grep('/^DEF:ds2/', $definition));
    }

    public function test_it_reports_no_data_when_no_series_has_an_rrd_file(): void
    {
        $this->rrdFiles->shouldReceive('pathFor')->with($this->metric)->andReturn(null);

        $this->expectException(NoGraphData::class);
        $this->expectExceptionMessageIsOrContains('No Data files');

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
