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
        // the next color after the series
        $this->assertSame('#000001', $lines[1]->color);
        $this->assertSame([2.0, 2.0, 5.0, 5.0], $lines[1]->avg);
        $this->assertNull($lines[1]->min);
        $this->assertTrue($lines[1]->invert, 'drawn on the side of its series');
    }

    public function test_series_with_percentile_lines_send_their_color(): void
    {
        $this->assertNull($this->read(new SeriesStyle)[0]->percentileColor);
        $this->assertSame('#000000', $this->read(new SeriesStyle(percentileLines: true))[0]->percentileColor);
        // a legend only series has no color of its own
        $this->assertSame('#AA0000', $this->read(new SeriesStyle(legendOnly: true, percentileLines: true))[0]->percentileColor);
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
