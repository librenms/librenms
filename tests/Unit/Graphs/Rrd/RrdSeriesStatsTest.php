<?php

namespace LibreNMS\Tests\Unit\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\RrdFileResolver;
use App\Graphs\Rrd\RrdSeriesDefinition;
use LibreNMS\Tests\TestCase;
use Mockery;

/**
 * Rolling averages and percentile lines of RrdSeriesDefinition, set by SeriesStyle
 */
class RrdSeriesStatsTest extends TestCase
{
    private const int EIGHT_DAYS = 691200;

    private MetricIdentity $metric;

    protected function setUp(): void
    {
        parent::setUp();
        $this->metric = new MetricIdentity('test-metric', ['hostname' => 'localhost']);
        LibrenmsConfig::shouldReceive('get')->byDefault()->andReturn(null);
        LibrenmsConfig::shouldReceive('has')->byDefault()->andReturn(true);
        // the fourth color of the palette is 000003, so the tests show which color each line takes
        LibrenmsConfig::shouldReceive('get')->with(Mockery::pattern('/^graph_colours\./'))->byDefault()
            ->andReturn(array_map(fn (int $index) => sprintf('%06d', $index), range(0, 9)));
    }

    public function test_it_adds_hourly_lines_and_percentiles(): void
    {
        $this->assertSame([
            "COMMENT:Offset                   Now      Min      Max      Avg\l",
            'DEF:ds0=host/test.rrd:offset:AVERAGE',
            'DEF:ds0min=host/test.rrd:offset:MIN',
            'DEF:ds0max=host/test.rrd:offset:MAX',
            'DEF:ds0hourly=host/test.rrd:offset:AVERAGE:step=3600',
            'DEF:ds0hourlymin=host/test.rrd:offset:MIN:step=3600',
            'DEF:ds0hourlymax=host/test.rrd:offset:MAX:step=3600',
            'VDEF:ds0p0=ds0,25,PERCENTNAN',
            'VDEF:ds0p1=ds0,50,PERCENTNAN',
            'VDEF:ds0p2=ds0,75,PERCENTNAN',
            'AREA:ds0#00000020',
            'LINE1.25:ds0#000000:Offset           ',
            'GPRINT:ds0:LAST:%5.2lf%ss',
            'GPRINT:ds0min:MIN:%5.2lf%ss',
            'GPRINT:ds0max:MAX:%5.2lf%ss',
            'GPRINT:ds0:AVERAGE:%5.2lf%ss\n',
            'LINE1.25:ds0hourly#000004:1 hour avg       ',
            'GPRINT:ds0hourly:LAST:%5.2lf%ss',
            'GPRINT:ds0hourly:MIN:%5.2lf%ss',
            'GPRINT:ds0hourly:MAX:%5.2lf%ss',
            'GPRINT:ds0hourly:AVERAGE:%5.2lf%ss\n',
            'COMMENT:  1 hour min       ',
            'GPRINT:ds0hourlymin:LAST:%5.2lf%ss',
            'GPRINT:ds0hourlymin:MIN:%5.2lf%ss',
            'GPRINT:ds0hourlymin:MAX:%5.2lf%ss',
            'GPRINT:ds0hourlymin:AVERAGE:%5.2lf%ss\n',
            'COMMENT:  1 hour max       ',
            'GPRINT:ds0hourlymax:LAST:%5.2lf%ss',
            'GPRINT:ds0hourlymax:MIN:%5.2lf%ss',
            'GPRINT:ds0hourlymax:MAX:%5.2lf%ss',
            'GPRINT:ds0hourlymax:AVERAGE:%5.2lf%ss\n',
            'HRULE:ds0p0#000001:25th Percentile  ',
            'GPRINT:ds0p0:%5.2lf%ss\n',
            'HRULE:ds0p1#000002:50th Percentile  ',
            'GPRINT:ds0p1:%5.2lf%ss\n',
            'HRULE:ds0p2#000003:75th Percentile  ',
            'GPRINT:ds0p2:%5.2lf%ss\n',
        ], $this->build(['offset' => new DataSeries($this->metric, 'offset', 'Offset', 's', style: $this->stats())], legendTitle: 'Offset'));
    }

    public function test_longer_periods_read_their_step(): void
    {
        $definition = $this->build($this->series(), range: self::EIGHT_DAYS);

        $this->assertContains('DEF:ds0daily=host/test.rrd:a:AVERAGE:step=86400', $definition);
        $this->assertContains('DEF:ds0weeklymin=host/test.rrd:a:MIN:step=604800', $definition);
        $this->assertContains('LINE1.25:ds0daily#000005:1 day avg        ', $definition);
        $this->assertContains('LINE1.25:ds0weekly#000006:1 week avg       ', $definition);
        $this->assertContains('COMMENT:  1 week max       ', $definition);
    }

    public function test_series_scale_applies_to_the_rolling_values(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'offset', 'Offset', scale: 0.001, style: $this->stats())]);

        $this->assertContains('DEF:ds0hourlymaxraw=host/test.rrd:offset:MAX:step=3600', $definition);
        $this->assertContains('CDEF:ds0hourlymax=ds0hourlymaxraw,0.001,*', $definition);
        $this->assertContains('VDEF:ds0p0=ds0,25,PERCENTNAN', $definition);
    }

    public function test_percent_in_the_unit_is_escaped(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', '%', style: $this->stats())]);

        foreach (['ds0hourly', 'ds0hourlymin', 'ds0hourlymax'] as $vname) {
            $this->assertContains("GPRINT:$vname:LAST:%5.2lf%s%%", $definition);
            $this->assertContains("GPRINT:$vname:MIN:%5.2lf%s%%", $definition);
            $this->assertContains("GPRINT:$vname:MAX:%5.2lf%s%%", $definition);
            $this->assertContains("GPRINT:$vname:AVERAGE:%5.2lf%s%%\\n", $definition);
        }
        $this->assertContains('GPRINT:ds0p0:%5.2lf%s%%\n', $definition);
    }

    public function test_colon_in_the_unit_is_escaped(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', 'a:b', style: $this->stats())]);

        foreach (['ds0hourly', 'ds0hourlymin', 'ds0hourlymax'] as $vname) {
            $this->assertContains("GPRINT:$vname:LAST:%5.2lf%sa\\:b", $definition);
            $this->assertContains("GPRINT:$vname:AVERAGE:%5.2lf%sa\\:b\\n", $definition);
        }
        $this->assertContains('GPRINT:ds0p0:%5.2lf%sa\:b\n', $definition);
    }

    public function test_series_without_stats_have_none(): void
    {
        $this->assertEmpty(preg_grep('/hourly|PERCENTNAN/', $this->build(['a' => new DataSeries($this->metric, 'a', 'A')], range: self::EIGHT_DAYS)));
    }

    public function test_fractional_percentiles_get_an_indexed_name(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(percentiles: [99.9]))]);

        $this->assertContains('VDEF:ds0p0=ds0,99.9,PERCENTNAN', $definition);
        $this->assertContains('HRULE:ds0p0#000001:99.9th Percentil ', $definition);
    }

    public function test_drawn_lines_follow_invert_and_draw_scale(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', style: new SeriesStyle(invert: true, drawScale: 2, rollingAverages: true, percentiles: [25]))]);

        $this->assertContains('CDEF:ds0hourlydrawn=ds0hourly,-2,*', $definition);
        $this->assertContains('LINE1.25:ds0hourlydrawn#000002:1 hour avg       ', $definition);
        $this->assertContains('GPRINT:ds0hourly:LAST:%5.2lf%s', $definition);
        $this->assertContains('CDEF:ds0p0drawn=ds0,POP,ds0p0,-2,*', $definition);
        $this->assertContains('HRULE:ds0p0line#000001:25th Percentile  ', $definition);
        $this->assertContains('GPRINT:ds0p0:%5.2lf%s\n', $definition);
    }

    public function test_short_graphs_have_no_stats(): void
    {
        $this->assertEmpty(preg_grep('/hourly|PERCENTNAN/', $this->build($this->series(), height: 25)));
        $this->assertNotEmpty(preg_grep('/hourly/', $this->build($this->series(), height: 26)));
    }

    private function stats(): SeriesStyle
    {
        return new SeriesStyle(fill: true, rollingAverages: true, percentiles: [25, 50, 75]);
    }

    /**
     * @return array<string, DataSeries>
     */
    private function series(): array
    {
        return ['a' => new DataSeries($this->metric, 'a', 'A', style: $this->stats())];
    }

    /**
     * @param  array<string, DataSeries>  $series
     * @param  int  $range  seconds covered by the graph
     * @return array<int, string>
     */
    private function build(array $series, int $range = 3600, int $height = 100, string $legendTitle = ''): array
    {
        $rrdFiles = Mockery::mock(RrdFileResolver::class);
        $rrdFiles->shouldReceive('pathFor')->andReturn('host/test.rrd');
        $params = new GraphParameters(['from' => 1000000, 'to' => 1000000 + $range, 'height' => $height]);

        return new RrdSeriesDefinition($series, new GraphOptions(legendTitle: $legendTitle), $rrdFiles)->build($params);
    }
}
