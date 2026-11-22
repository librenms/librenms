<?php

namespace LibreNMS\Tests\Unit\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\Formula;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\SeriesSum;
use App\Data\Graphing\Threshold;
use App\Data\Graphing\TrafficOptions;
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
        LibrenmsConfig::shouldReceive('get')->with(Mockery::pattern('/^graph_colours\./'))->byDefault()->andReturn(['FF0000']);
    }

    public function test_it_draws_lines_with_fill_and_invert(): void
    {
        $this->assertSame([
            'COMMENT:Traffic            ',
            "COMMENT:         Now         Min         Max         Avg\l",
            'DEF:ds0=host/test.rrd:in:AVERAGE',
            'DEF:ds0min=host/test.rrd:in:MIN',
            'DEF:ds0max=host/test.rrd:in:MAX',
            'DEF:ds1=host/test.rrd:out:AVERAGE',
            'DEF:ds1min=host/test.rrd:out:MIN',
            'DEF:ds1max=host/test.rrd:out:MAX',
            'CDEF:ds1drawn=ds1,-1,*',
            'AREA:ds0#FF000020',
            'AREA:ds1drawn#D42E08',
            'LINE1.25:ds0#FF0000:In               ',
            'GPRINT:ds0:LAST: %7.2lf%sbps\g',
            'GPRINT:ds0min:MIN: %7.2lf%sbps\g',
            'GPRINT:ds0max:MAX: %7.2lf%sbps\g',
            'GPRINT:ds0:AVERAGE: %7.2lf%sbps\n',
            'LINE1.25:ds1drawn#D42E08:Out              ',
            'GPRINT:ds1:LAST: %7.2lf%sbps\g',
            'GPRINT:ds1min:MIN: %7.2lf%sbps\g',
            'GPRINT:ds1max:MAX: %7.2lf%sbps\g',
            'GPRINT:ds1:AVERAGE: %7.2lf%sbps\n',
            'HRULE:0#555555',
        ], $this->build([
            'in' => new DataSeries($this->metric, 'in', 'In', 'bps', style: new SeriesStyle(fill: true)),
            'out' => new DataSeries($this->metric, 'out', 'Out', 'bps', style: new SeriesStyle(SeriesRole::Loss, fill: true, invert: true)),
        ], new GraphOptions(legendTitle: 'Traffic')));
    }

    public function test_max_fills_are_drawn_behind_the_fills(): void
    {
        $definition = $this->build([
            'in' => new DataSeries($this->metric, 'in', 'In', scale: 8, style: new SeriesStyle(SeriesRole::In, fill: true, maxFill: true)),
            'out' => new DataSeries($this->metric, 'out', 'Out', style: new SeriesStyle(SeriesRole::Out, maxFill: true, invert: true)),
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
            'COMMENT:Load %             ',
            "COMMENT:      Now      Min      Max      Avg\l",
            'DEF:ds0=host/test.rrd:usage:AVERAGE',
            'DEF:ds0min=host/test.rrd:usage:MIN',
            'DEF:ds0max=host/test.rrd:usage:MAX',
            'CDEF:ds0drawn=ds0,0.5,*',
            'DEF:ds1=host/test.rrd:usage:AVERAGE',
            'DEF:ds1min=host/test.rrd:usage:MIN',
            'DEF:ds1max=host/test.rrd:usage:MAX',
            'CDEF:ds1drawn=ds1,0.5,*',
            'AREA:ds0drawn#FF0000:A                ',
            'GPRINT:ds0:LAST: %7.2lf%s\g',
            'GPRINT:ds0min:MIN: %7.2lf%s\g',
            'GPRINT:ds0max:MAX: %7.2lf%s\g',
            'GPRINT:ds0:AVERAGE: %7.2lf%s\n',
            'AREA:ds1drawn#FF0000:B                :STACK',
            'GPRINT:ds1:LAST: %7.2lf%s\g',
            'GPRINT:ds1min:MIN: %7.2lf%s\g',
            'GPRINT:ds1max:MAX: %7.2lf%s\g',
            'GPRINT:ds1:AVERAGE: %7.2lf%s\n',
        ], $this->build([
            'a' => new DataSeries($this->metric, 'usage', 'A', style: new SeriesStyle(stacked: true, drawScale: 0.5)),
            'b' => new DataSeries($this->metric, 'usage', 'B', style: new SeriesStyle(stacked: true, drawScale: 0.5)),
        ], new GraphOptions(legendTitle: 'Load %')));
    }

    public function test_series_scale_changes_the_value_everywhere(): void
    {
        $this->assertSame([
            'COMMENT:                   ',
            "COMMENT:      Now      Min      Max      Avg\l",
            'DEF:ds0raw=host/test.rrd:bytes:AVERAGE',
            'CDEF:ds0=ds0raw,8,*',
            'DEF:ds0minraw=host/test.rrd:bytes:MIN',
            'CDEF:ds0min=ds0minraw,8,*',
            'DEF:ds0maxraw=host/test.rrd:bytes:MAX',
            'CDEF:ds0max=ds0maxraw,8,*',
            'LINE1.25:ds0#FF0000:Bits             ',
            'GPRINT:ds0:LAST: %7.2lf%s\g',
            'GPRINT:ds0min:MIN: %7.2lf%s\g',
            'GPRINT:ds0max:MAX: %7.2lf%s\g',
            'GPRINT:ds0:AVERAGE: %7.2lf%s\n',
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
        ], array_slice($definition, 2, 6));
        $this->assertContains('GPRINT:ds0min:MIN: %7.2lf%s%%\g', $definition);
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
            'rtt' => new DataSeries($this->metric, 'avg', 'RTT', minField: 'min', maxField: 'max', style: new SeriesStyle(SeriesRole::Value, range: true, invert: true)),
        ]);

        $this->assertContains('DEF:ds0=host/test.rrd:avg:AVERAGE', $definition);
        $this->assertContains('DEF:ds0min=host/test.rrd:min:MIN', $definition);
        $this->assertContains('DEF:ds0max=host/test.rrd:max:MAX', $definition);
        $this->assertContains('CDEF:ds0range=ds0maxdrawn,ds0mindrawn,-', $definition);
        $drawing = array_values(preg_grep('/^(AREA|LINE)/', $definition));
        $this->assertSame(['LINE:ds0mindrawn#00000000', 'AREA:ds0range#D0D0D0::STACK', 'LINE1.25:ds0drawn#272B30:RTT              '], $drawing);
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

    public function test_stacked_series_may_be_listed_in_any_order(): void
    {
        $definition = $this->build([
            'in_a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(stacked: true)),
            'out_a' => new DataSeries($this->metric, 'c', 'C', style: new SeriesStyle(stacked: true, invert: true)),
            'in_b' => new DataSeries($this->metric, 'b', 'B', style: new SeriesStyle(stacked: true)),
            'in_c' => new DataSeries($this->metric, 'b', 'BB', style: new SeriesStyle(stacked: true)),
            'out_b' => new DataSeries($this->metric, 'd', 'D', style: new SeriesStyle(stacked: true, invert: true)),
            'line' => new DataSeries($this->metric, 'e', 'E'),
            'out_c' => new DataSeries($this->metric, 'd', 'DD', style: new SeriesStyle(stacked: true, invert: true)),
        ]);

        // each series rests on the top of its own stack, unknown values add nothing
        $this->assertContains('CDEF:ds2base=0,ds0,ADDNAN', $definition);
        $this->assertContains('CDEF:ds4base=0,ds1drawn,ADDNAN', $definition);
        $this->assertContains('CDEF:ds6base=ds4base,ds4drawn,ADDNAN', $definition);
        $this->assertSame([
            'AREA:ds0#FF0000:A                ',
            'AREA:ds1drawn#FF0000:C                ',
            'LINE:ds2base#00000000',
            'AREA:ds2#FF0000:B                :STACK',
            'AREA:ds3#FF0000:BB               :STACK',
            'LINE:ds4base#00000000',
            'AREA:ds4drawn#FF0000:D                :STACK',
            'LINE1.25:ds5#FF0000:E                ',
            'LINE:ds6base#00000000',
            'AREA:ds6drawn#FF0000:DD               :STACK',
        ], array_values(preg_grep('/^(AREA|LINE)/', $definition)));
    }

    public function test_sums_add_up_series_listed_before_them(): void
    {
        $missing = new MetricIdentity('missing', ['hostname' => 'localhost']);
        $this->rrdFiles->shouldReceive('pathFor')->with($missing)->andReturn(null);

        $definition = $this->build([
            'a' => new DataSeries($this->metric, 'a', 'A', scale: 8),
            'gone' => new DataSeries($missing, 'g', 'Gone'),
            'b' => new DataSeries($this->metric, 'b', 'B'),
            'sum' => new SeriesSum(['a', 'gone', 'b'], 'Sum', 'bps'),
            'nested' => new SeriesSum(['sum', 'a'], 'Nested', style: new SeriesStyle(legendOnly: true)),
            'empty' => new SeriesSum(['gone'], 'Empty'),
            'later' => new SeriesSum(['after'], 'Later'),
            'after' => new DataSeries($this->metric, 'c', 'After'),
        ]);

        $this->assertContains('CDEF:ds2=ds0,ds1,ADDNAN', $definition);
        // the lowest and highest of a sum are those of its averages
        $this->assertContains('CDEF:ds2min=ds0,ds1,ADDNAN', $definition);
        $this->assertContains('CDEF:ds2max=ds0,ds1,ADDNAN', $definition);
        $this->assertContains('LINE1.25:ds2#FF0000:Sum              ', $definition);
        $this->assertContains('GPRINT:ds2max:MAX: %7.2lf%sbps\g', $definition);
        $this->assertContains('CDEF:ds3=ds2,ds0,ADDNAN', $definition);
        $this->assertContains('HRULE:0#00000000:Nested           ', $definition);
        $this->assertEmpty(preg_grep('/^(AREA|LINE).*ds3/', $definition));
        $this->assertEmpty(preg_grep('/Empty|Later/', $definition));
        $this->assertContains('DEF:ds4=host/test.rrd:c:AVERAGE', $definition);
        $this->assertSame(array_unique($definition), $definition);
    }

    public function test_traffic_summarizes_the_selected_series_and_the_previous_period_of_sums(): void
    {
        $definition = $this->build([
            'in_a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(stacked: true)),
            'in_b' => new DataSeries($this->metric, 'b', 'B', style: new SeriesStyle(stacked: true)),
            'in' => new SeriesSum(['in_a', 'in_b'], 'In', style: new SeriesStyle(legendOnly: true, percentileLines: true)),
        ], new GraphOptions(traffic: new TrafficOptions(series: ['in']), percentiles: [95]), ['from' => 100, 'to' => 200, 'previous' => 'yes']);

        $this->assertContains('VDEF:ds2p0=ds2,95,PERCENTNAN', $definition);
        // every row has the percentile column, the previous period too
        $this->assertContains('GPRINT:ds1p0: %7.2lf%s\n', $definition);
        $this->assertContains('GPRINT:ds2p0: %7.2lf%s\n', $definition);
        $this->assertContains('GPRINT:ds2prevp0: %7.2lf%s\n', $definition);
        // only the sum draws its percentile, and that of its previous period in the color of the previous period
        $this->assertSame(['HRULE:ds2p0#AA0000::dashes', 'HRULE:ds2prevp0#333300::dashes'], array_values(preg_grep('/dashes$/', $definition)));
        // the previous period of the sum is the sum of the previous period of its series, defined once
        $this->assertContains('CDEF:ds2prev=ds0prev,ds1prev,ADDNAN', $definition);
        $this->assertContains('CDEF:ds2prevmax=ds0prev,ds1prev,ADDNAN', $definition);
        $this->assertCount(1, preg_grep('/^DEF:ds0prev=/', $definition));
        $this->assertSame(array_unique($definition), $definition);
    }

    public function test_thresholds_are_dashed_lines(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A')], new GraphOptions(thresholds: [
            new Threshold(-2.5, 'low = -2.5 dBm', SeriesRole::LowLimit),
            new Threshold(80, 'high: 80 °C', SeriesRole::HighLimit),
        ]));

        $this->assertContains('LINE1.5:-2.5#00008B:low = -2.5 dBm:dashes', $definition);
        $this->assertContains('LINE1.5:80#FF0000:high\: 80 °C\l:dashes', $definition);
    }

    public function test_shown_thresholds_are_kept_inside_the_axis_and_hidden_ones_are_not_drawn(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A')], new GraphOptions(thresholds: [
            new Threshold(10, 'low', SeriesRole::LowLimit),
            new Threshold(50, 'high', SeriesRole::HighLimit),
            new Threshold(500, 'far', SeriesRole::HighWarning, shown: false),
        ]));

        $this->assertContains('LINE:8#00000000', $definition);
        $this->assertContains('LINE:52#00000000', $definition);
        $this->assertContains('LINE1.5:50#FF0000:high\\l:dashes', $definition, 'the last shown threshold ends the legend line');
        $this->assertEmpty(preg_grep('/far|500/', $definition));
    }

    public function test_inverted_and_mirrored_thresholds_are_drawn_below_the_axis_with_inverted_series(): void
    {
        $series = ['out' => new DataSeries($this->metric, 'a', 'Out', style: new SeriesStyle(invert: true))];
        $options = new GraphOptions(thresholds: [
            new Threshold(10, 'out', SeriesRole::PortSpeed, invert: true),
            new Threshold(20, 'both', SeriesRole::PortSpeed, mirror: true),
        ]);

        $definition = $this->build($series, $options);
        $this->assertContains('LINE1.5:-10#000000:out:dashes', $definition);
        $this->assertContains('LINE1.5:20#000000:both\\l:dashes', $definition);
        $this->assertContains('LINE1.5:-20#000000::dashes', $definition, 'the mirrored line is in the legend once');
        $this->assertContains('LINE:-22#00000000', $definition);

        LibrenmsConfig::shouldReceive('get')->with('webui.graph_stacked')->andReturn(true);
        $stacked = $this->build($series, $options);
        $this->assertContains('LINE1.5:10#000000:out:dashes', $stacked, 'drawn above the axis with the series');
        $this->assertEmpty(preg_grep('/^LINE1.5:-/', $stacked));
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

        $this->assertContains('GPRINT:ds0:LAST: %7.2lf ms\g', $definition);
    }

    public function test_roles_use_the_colors_of_the_style_of_the_request(): void
    {
        $series = ['a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(SeriesRole::Value, range: true))];

        $light = $this->build($series, params: ['from' => 100, 'to' => 200, 'style' => 'light']);
        $this->assertContains('LINE1.25:ds0#272B30:A                ', $light);
        $this->assertContains('AREA:ds0range#D0D0D0::STACK', $light);

        $dark = $this->build($series, params: ['from' => 100, 'to' => 200, 'style' => 'dark']);
        $this->assertContains('LINE1.25:ds0#F2F2F2:A                ', $dark);
        $this->assertContains('AREA:ds0range#3E444C::STACK', $dark);
    }

    public function test_colors_go_only_to_drawn_series_and_vary_per_role(): void
    {
        $missing = new MetricIdentity('missing', ['hostname' => 'localhost']);
        $this->rrdFiles->shouldReceive('pathFor')->with($missing)->andReturn(null);
        LibrenmsConfig::shouldReceive('get')->with('graph_colours.greens')->andReturn(['00AA00', '00BB00']);
        LibrenmsConfig::shouldReceive('get')->with('graph_colours.purples')->andReturn(['AA00AA', 'BB00BB']);

        $definition = $this->build([
            'in_gone' => new DataSeries($missing, 'a', 'Gone In', style: new SeriesStyle(SeriesRole::In, stacked: true)),
            'out_gone' => new DataSeries($missing, 'b', 'Gone Out', style: new SeriesStyle(SeriesRole::Out, stacked: true, invert: true)),
            'in_a' => new DataSeries($this->metric, 'a', 'A In', style: new SeriesStyle(SeriesRole::In, stacked: true)),
            'out_a' => new DataSeries($this->metric, 'b', 'A Out', style: new SeriesStyle(SeriesRole::Out, stacked: true, invert: true)),
            'in_b' => new DataSeries($this->metric, 'c', 'B In', style: new SeriesStyle(SeriesRole::In, stacked: true)),
            'out_b' => new DataSeries($this->metric, 'd', 'B Out', style: new SeriesStyle(SeriesRole::Out, stacked: true, invert: true)),
            'in_c' => new DataSeries($this->metric, 'e', 'C In', style: new SeriesStyle(SeriesRole::In, stacked: true)),
        ]);

        $areas = array_map(fn ($area) => substr($area, 0, strpos($area, ':', 5)), array_values(preg_grep('/^AREA/', $definition)));
        $this->assertSame(['AREA:ds0#00AA00', 'AREA:ds1drawn#AA00AA', 'AREA:ds2#00BB00', 'AREA:ds3drawn#BB00BB', 'AREA:ds4#00AA00'], $areas);
    }

    public function test_overlapping_areas_and_fills_are_translucent(): void
    {
        LibrenmsConfig::shouldReceive('get')->with('webui.graph_stacked')->andReturn(true);

        $definition = $this->build([
            'in' => new DataSeries($this->metric, 'a', 'In', style: new SeriesStyle(SeriesRole::In, fill: true, maxFill: true)),
            'out' => new DataSeries($this->metric, 'b', 'Out', style: new SeriesStyle(SeriesRole::Out, fill: true, invert: true)),
            'errors' => new DataSeries($this->metric, 'c', 'Errors', style: new SeriesStyle(SeriesRole::Error, stacked: true)),
        ]);

        $this->assertContains('AREA:ds0max#D7FFC788', $definition);
        $this->assertContains('AREA:ds0#90B04088', $definition);
        $this->assertContains('AREA:ds1#8080C088', $definition);
        $this->assertContains('LINE1.25:ds1#606090:Out              ', $definition);
        $this->assertContains('AREA:ds2#FF330088:Errors           ', $definition);
    }

    public function test_a_drawn_inverted_sum_makes_areas_translucent(): void
    {
        LibrenmsConfig::shouldReceive('get')->with('webui.graph_stacked')->andReturn(true);
        LibrenmsConfig::shouldReceive('get')->with('graph_colours.greens')->andReturn(['00AA00']);
        LibrenmsConfig::shouldReceive('get')->with('graph_colours.purples')->andReturn(['AA00AA']);

        $definition = $this->build([
            'in' => new DataSeries($this->metric, 'a', 'In', style: new SeriesStyle(SeriesRole::In, stacked: true)),
            'out' => new DataSeries($this->metric, 'b', 'Out', style: new SeriesStyle(legendOnly: true)),
            'total' => new SeriesSum(['out'], 'Total', style: new SeriesStyle(SeriesRole::Out, stacked: true, invert: true)),
        ]);

        $this->assertContains('AREA:ds0#00AA0088:In               ', $definition);
        $this->assertContains('AREA:ds2#AA00AA88:Total            :STACK', $definition);
    }

    public function test_areas_without_overlapping_series_are_opaque(): void
    {
        LibrenmsConfig::shouldReceive('get')->with('webui.graph_stacked')->andReturn(true);
        $missing = new MetricIdentity('missing', ['hostname' => 'localhost']);
        $this->rrdFiles->shouldReceive('pathFor')->with($missing)->andReturn(null);
        $legendOnly = new SeriesStyle(invert: true, legendOnly: true);

        $definition = $this->build([
            'used' => new DataSeries($this->metric, 'a', 'Used', style: new SeriesStyle(SeriesRole::usage(90, 80), stacked: true)),
            'free' => new DataSeries($this->metric, 'b', 'Free', style: new SeriesStyle(SeriesRole::Muted, stacked: true)),
            'legend' => new DataSeries($this->metric, 'c', 'Legend', style: $legendOnly),
            'total' => new SeriesSum(['used', 'free'], 'Total', style: $legendOnly),
            'missing' => new DataSeries($missing, 'd', 'Missing'),
            'empty' => new SeriesSum(['missing'], 'Empty', style: new SeriesStyle(stacked: true, invert: true)),
        ]);

        $this->assertContains('AREA:ds0#C75862:Used             ', $definition);
        $this->assertContains('AREA:ds1#D0D0D0:Free             :STACK', $definition);
        $this->assertEmpty(preg_grep('/Empty/', $definition));
    }

    public function test_the_axis_and_reference_lines_follow_the_dark_style(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(invert: true))], new GraphOptions(thresholds: [
            new Threshold(1, 'low', SeriesRole::LowLimit),
        ]), ['from' => 100, 'to' => 200, 'style' => 'dark']);

        $this->assertContains('HRULE:0#999999', $definition);
        $this->assertContains('LINE1.5:1#7A8CFF:low\l:dashes', $definition);
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
        LibrenmsConfig::shouldReceive('get')->with('graph_colours.oranges')->andReturn(['EE7700']);

        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A')], new GraphOptions(palette: 'oranges'));

        $this->assertContains('LINE1.25:ds0#EE7700:A                ', $definition);
    }

    public function test_it_escapes_units_in_value_formats_and_widens_the_columns(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', 'a%:b')]);

        $this->assertContains('GPRINT:ds0:LAST: %7.2lf%sa%%\:b\g', $definition);
        $this->assertSame("COMMENT:          Now          Min          Max          Avg\l", $definition[1]);
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
     * @param  array<string, DataSeries|SeriesSum>  $series
     * @param  array<string, mixed>  $params
     * @return array<int, string>
     */
    private function build(array $series, GraphOptions $options = new GraphOptions, array $params = ['from' => 100, 'to' => 200]): array
    {
        return new RrdSeriesDefinition($series, $options, $this->rrdFiles)->build(new GraphParameters($params));
    }
}
