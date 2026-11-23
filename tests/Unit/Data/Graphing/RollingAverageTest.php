<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\RollingAverage;
use App\Data\Graphing\SeriesStyle;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\GraphTheme;
use LibreNMS\Tests\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

class RollingAverageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        LibrenmsConfig::set('graph_colours.test', ['000000', '000001', '000002', '000003']);
    }

    public function test_a_plain_style_has_none_and_takes_no_colors(): void
    {
        $theme = new GraphTheme(false, 'test');

        $this->assertSame([], RollingAverage::forStyle(new SeriesStyle, $theme, 691200));
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
        $averages = RollingAverage::forStyle(new SeriesStyle(rollingAverages: true), new GraphTheme(false, 'test'), $range);

        $this->assertSame($names, array_map(fn (RollingAverage $average) => $average->name, $averages));
    }

    public function test_each_keeps_its_color_whatever_is_left_out(): void
    {
        $theme = new GraphTheme(false, 'test');
        $hourly = RollingAverage::forStyle(new SeriesStyle(rollingAverages: true), $theme, 3600)[0];

        $this->assertSame(['hourly', '1 hour avg', 3600, '000000'], [$hourly->name, $hourly->label, $hourly->step, $hourly->color]);
        $this->assertSame('000003', $theme->next(null), 'the daily and weekly colors are taken');
    }
}
