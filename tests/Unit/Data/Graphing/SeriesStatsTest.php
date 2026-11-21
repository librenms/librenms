<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\SeriesStats;
use App\Data\Graphing\SeriesStyle;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\GraphTheme;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

class SeriesStatsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('graph_colours.test', ['000000', '000001', '000002', '000003', '000004', '000005', '000006']);
        LibrenmsConfig::set('graph_stat_percentile_disable', false);
    }

    public function test_a_plain_style_has_no_lines_and_takes_no_colors(): void
    {
        $theme = new GraphTheme(false, 'test');
        $stats = SeriesStats::of(new SeriesStyle, $theme, 691200);

        $this->assertSame([], $stats->averages);
        $this->assertSame([], $stats->percentiles);
        $this->assertSame('000000', $theme->next(null));
    }

    /**
     * @param  list<string>  $names
     */
    #[TestWith([3600, ['hourly']])]
    #[TestWith([129599, ['hourly']])]
    #[TestWith([129600, ['hourly', 'daily']])]
    #[TestWith([691200, ['hourly', 'daily', 'weekly']])]
    public function test_longer_periods_need_a_longer_graph(int $range, array $names): void
    {
        $this->assertSame($names, array_keys(SeriesStats::of(new SeriesStyle(rollingAverages: true), new GraphTheme(false, 'test'), $range)->averages));
    }

    public function test_colors_are_in_the_legacy_order_whatever_is_left_out(): void
    {
        $stats = SeriesStats::of(new SeriesStyle(rollingAverages: true, percentiles: [25, 99.9]), new GraphTheme(false, 'test'), 3600);

        $this->assertSame(['label' => '1 hour avg', 'period' => '1 hour', 'step' => 3600, 'color' => '000002'], $stats->averages['hourly']);
        $this->assertSame([
            ['percentile' => 25, 'label' => '25th Percentile', 'color' => '000000'],
            ['percentile' => 99.9, 'label' => '99.9th Percentile', 'color' => '000001'],
        ], $stats->percentiles);
    }

    public function test_percentiles_can_be_disabled_by_config_and_keep_their_colors(): void
    {
        LibrenmsConfig::set('graph_stat_percentile_disable', true);

        $stats = SeriesStats::of(new SeriesStyle(rollingAverages: true, percentiles: [50]), new GraphTheme(false, 'test'), 3600);

        $this->assertSame([], $stats->percentiles);
        $this->assertSame('000001', $stats->averages['hourly']['color']);
    }
}
