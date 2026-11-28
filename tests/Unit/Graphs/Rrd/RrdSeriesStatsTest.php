<?php

namespace LibreNMS\Tests\Unit\Graphs\Rrd;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\SeriesSum;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\RrdFileResolver;
use App\Graphs\Rrd\RrdSeriesDefinition;
use LibreNMS\Tests\TestCase;
use Mockery;

/**
 * Percentiles of GraphOptions and rolling averages of SeriesStyle drawn by RrdSeriesDefinition
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

    public function test_percentiles_are_legend_columns_and_dashed_lines(): void
    {
        $this->assertSame([
            'COMMENT:Offset             ',
            "COMMENT:       Now       Min       Max       Avg      25th      50th      75th\l",
            'DEF:ds0=host/test.rrd:offset:AVERAGE',
            'DEF:ds0min=host/test.rrd:offset:MIN',
            'DEF:ds0max=host/test.rrd:offset:MAX',
            'DEF:ds0hourly=host/test.rrd:offset:AVERAGE:step=3600',
            'VDEF:ds0p0=ds0,25,PERCENTNAN',
            'VDEF:ds0p1=ds0,50,PERCENTNAN',
            'VDEF:ds0p2=ds0,75,PERCENTNAN',
            'VDEF:ds0hourlyp0=ds0hourly,25,PERCENTNAN',
            'VDEF:ds0hourlyp1=ds0hourly,50,PERCENTNAN',
            'VDEF:ds0hourlyp2=ds0hourly,75,PERCENTNAN',
            'AREA:ds0#00000020',
            'LINE1.25:ds0#000000:Offset           ',
            'GPRINT:ds0:LAST: %7.2lf%ss\g',
            'GPRINT:ds0min:MIN: %7.2lf%ss\g',
            'GPRINT:ds0max:MAX: %7.2lf%ss\g',
            'GPRINT:ds0:AVERAGE: %7.2lf%ss\g',
            'GPRINT:ds0p0: %7.2lf%ss\g',
            'GPRINT:ds0p1: %7.2lf%ss\g',
            'GPRINT:ds0p2: %7.2lf%ss\n',
            'HRULE:ds0p0#000000::dashes',
            'HRULE:ds0p1#000000::dashes',
            'HRULE:ds0p2#000000::dashes',
            'LINE1.25:ds0hourly#000001:1 hour avg       ',
            'GPRINT:ds0hourly:LAST: %7.2lf%ss\g',
            'GPRINT:ds0hourly:MIN: %7.2lf%ss\g',
            'GPRINT:ds0hourly:MAX: %7.2lf%ss\g',
            'GPRINT:ds0hourly:AVERAGE: %7.2lf%ss\g',
            'GPRINT:ds0hourlyp0: %7.2lf%ss\g',
            'GPRINT:ds0hourlyp1: %7.2lf%ss\g',
            'GPRINT:ds0hourlyp2: %7.2lf%ss\n',
        ], $this->build(['offset' => new DataSeries($this->metric, 'offset', 'Offset', 's', style: $this->stats(fill: true))], legendTitle: 'Offset'));
    }

    public function test_every_row_gets_the_percentile_columns_and_only_asked_series_draw_them(): void
    {
        $definition = $this->build([
            'a' => new DataSeries($this->metric, 'a', 'A'),
            'b' => new DataSeries($this->metric, 'b', 'B', style: new SeriesStyle(percentileLines: true)),
        ], percentiles: [95]);

        $this->assertContains('GPRINT:ds0p0: %7.2lf%s\n', $definition);
        $this->assertContains('GPRINT:ds1p0: %7.2lf%s\n', $definition);
        $this->assertSame(['HRULE:ds1p0#000001::dashes'], array_values(preg_grep('/^HRULE/', $definition)));
    }

    public function test_longer_periods_read_their_step(): void
    {
        $definition = $this->build($this->series(), range: self::EIGHT_DAYS);

        $this->assertContains('DEF:ds0daily=host/test.rrd:a:AVERAGE:step=86400', $definition);
        $this->assertContains('DEF:ds0weekly=host/test.rrd:a:AVERAGE:step=604800', $definition);
        $this->assertContains('LINE1.25:ds0daily#000002:1 day avg        ', $definition);
        $this->assertContains('LINE1.25:ds0weekly#000003:1 week avg       ', $definition);
    }

    public function test_series_scale_applies_to_the_rolling_values(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'offset', 'Offset', scale: 0.001, style: $this->stats())]);

        $this->assertContains('DEF:ds0hourlyraw=host/test.rrd:offset:AVERAGE:step=3600', $definition);
        $this->assertContains('CDEF:ds0hourly=ds0hourlyraw,0.001,*', $definition);
    }

    public function test_units_are_escaped_in_the_percentile_columns(): void
    {
        $this->assertContains('GPRINT:ds0p2: %7.2lf%s%%\n', $this->build(['a' => new DataSeries($this->metric, 'a', 'A', '%', style: $this->stats())]));
        $this->assertContains('GPRINT:ds0p2: %7.2lf%sa\:b\n', $this->build(['a' => new DataSeries($this->metric, 'a', 'A', 'a:b', style: $this->stats())]));
    }

    public function test_fractional_percentiles_get_an_indexed_name(): void
    {
        $definition = $this->build($this->series(), percentiles: [99.9]);

        $this->assertContains('VDEF:ds0p0=ds0,99.9,PERCENTNAN', $definition);
        $this->assertStringContainsString('99.9th', $definition[1]);
    }

    public function test_drawn_lines_follow_invert_and_draw_scale(): void
    {
        $definition = $this->build(['a' => new DataSeries($this->metric, 'a', 'A', style: $this->stats(invert: true, drawScale: 2))], percentiles: [25]);

        $this->assertContains('CDEF:ds0hourlydrawn=ds0hourly,-2,*', $definition);
        $this->assertContains('LINE1.25:ds0hourlydrawn#000001:1 hour avg       ', $definition);
        $this->assertContains('CDEF:ds0p0drawn=ds0,POP,ds0p0,-2,*', $definition);
        $this->assertContains('VDEF:ds0p0line=ds0p0drawn,FIRST', $definition);
        $this->assertContains('HRULE:ds0p0line#000000::dashes', $definition);
        // the legend shows the value of the series
        $this->assertContains('GPRINT:ds0p0: %7.2lf%s\n', $definition);
    }

    public function test_legend_only_series_draw_percentiles_in_the_percentile_color(): void
    {
        $definition = $this->build([
            'a' => new DataSeries($this->metric, 'a', 'A'),
            'total' => new SeriesSum(['a'], 'Total', style: new SeriesStyle(legendOnly: true, percentileLines: true)),
        ], percentiles: [95]);

        $this->assertSame(['HRULE:ds1p0#AA0000::dashes'], array_values(preg_grep('/dashes/', $definition)));
    }

    public function test_short_graphs_keep_the_columns_without_extra_lines(): void
    {
        $definition = $this->build($this->series(), height: 25);

        $this->assertEmpty(preg_grep('/hourly|HRULE/', $definition));
        $this->assertContains('GPRINT:ds0p0: %7.2lf%s\g', $definition);
        $this->assertNotEmpty(preg_grep('/hourly/', $this->build($this->series(), height: 26)));
    }

    private function stats(bool $fill = false, bool $invert = false, float $drawScale = 1): SeriesStyle
    {
        return new SeriesStyle(fill: $fill, invert: $invert, drawScale: $drawScale, rollingAverages: true, percentileLines: true);
    }

    /**
     * @return array<string, DataSeries>
     */
    private function series(): array
    {
        return ['a' => new DataSeries($this->metric, 'a', 'A', style: $this->stats())];
    }

    /**
     * @param  array<string, DataSeries|SeriesSum>  $series
     * @param  int  $range  seconds covered by the graph
     * @param  list<int|float>  $percentiles
     * @return array<int, string>
     */
    private function build(array $series, int $range = 3600, int $height = 100, string $legendTitle = '', array $percentiles = [25, 50, 75]): array
    {
        $rrdFiles = Mockery::mock(RrdFileResolver::class);
        $rrdFiles->shouldReceive('pathFor')->andReturn('host/test.rrd');
        // wide enough for every legend column
        $params = new GraphParameters(['from' => 1000000, 'to' => 1000000 + $range, 'height' => $height, 'width' => 1000]);

        return new RrdSeriesDefinition($series, new GraphOptions(legendTitle: $legendTitle, percentiles: $percentiles), $rrdFiles)->build($params);
    }
}
