<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\GraphDataSeries;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;
use App\Data\TimeSeries\SeriesData;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\GraphTheme;
use LibreNMS\Tests\TestCase;

class GraphDataSeriesTest extends TestCase
{
    // two hours of half hour points, the first hour ends at 7200
    private const array TIMESTAMPS = [5400, 7200, 9000, 10800];

    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('graph_colours.test', ['000000', '000001', '000002', '000003', '000004', '000005', '000006']);
        LibrenmsConfig::set('graph_stat_percentile_disable', false);
    }

    public function test_series_without_stats_have_no_extra_lines(): void
    {
        $this->assertSame(['a'], array_column($this->read(new SeriesStyle), 'key'));
    }

    public function test_rolling_averages_follow_their_series_and_average_each_period(): void
    {
        $lines = $this->read(new SeriesStyle(invert: true, rollingAverages: true));

        $this->assertSame(['a', 'a.hourly'], array_column($lines, 'key'));
        $this->assertSame('1 hour avg', $lines[1]->label);
        $this->assertSame('#000001', $lines[1]->color);
        $this->assertSame([2.0, 2.0, 5.0, 5.0], $lines[1]->avg);
        $this->assertNull($lines[1]->min);
        $this->assertTrue($lines[1]->invert, 'drawn on the side of its series');
    }

    public function test_percentiles_are_lines_at_their_nearest_rank(): void
    {
        $lines = $this->read(new SeriesStyle(rollingAverages: true, percentiles: [25, 50]));

        $this->assertSame(['a', 'a.hourly', 'a.p0', 'a.p1'], array_column($lines, 'key'));
        $this->assertSame(['25th Percentile', '50th Percentile'], [$lines[2]->label, $lines[3]->label]);
        $this->assertSame([1.0, 1.0, 1.0, 1.0], $lines[2]->avg);
        $this->assertSame([3.0, 3.0, 3.0, 3.0], $lines[3]->avg);
    }

    /**
     * Values 1 and 3 in the first hour and 5 in the second, with a gap
     *
     * @return list<GraphDataSeries>
     */
    private function read(SeriesStyle $style): array
    {
        $series = ['a' => new DataSeries(new MetricIdentity('test', []), 'a', 'A', style: $style)];
        $data = new SeriesData(self::TIMESTAMPS, ['a' => ['avg' => [1.0, 3.0, 5.0, null], 'min' => null, 'max' => null]]);

        return GraphDataSeries::fromReadings($series, $data, new GraphTheme(false, 'test'), true, 7200);
    }
}
