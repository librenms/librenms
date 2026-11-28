<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\ResolvedStyle;
use App\Data\Graphing\SeriesRole;
use App\Data\Graphing\SeriesStyle;
use App\Data\Graphing\SeriesSum;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\GraphTheme;
use LibreNMS\Tests\TestCase;

class ResolvedStyleTest extends TestCase
{
    public function test_drawn_series_take_the_colors_of_their_role_in_order(): void
    {
        LibrenmsConfig::set('graph_colours.test', ['aa0000', 'bb0000']);

        $styles = ResolvedStyle::resolve([
            'a' => $this->series(),
            'errors' => $this->series(new SeriesStyle(role: SeriesRole::Error)),
            'total' => new SeriesSum(['a'], 'Total', style: new SeriesStyle(legendOnly: true)),
            'b' => $this->series(),
            'c' => $this->series(),
        ], new GraphTheme(false, 'test'));

        $this->assertSame(['a' => 'aa0000', 'errors' => 'FF3300', 'total' => null, 'b' => 'bb0000', 'c' => 'aa0000'], array_map(fn (ResolvedStyle $style) => $style->color, $styles));
        $this->assertTrue($styles['total']->legendOnly, 'legend only series are not drawn and take no color');
    }

    public function test_fills_and_ranges(): void
    {
        $theme = new GraphTheme(false);
        $styles = ResolvedStyle::resolve([
            'line' => $this->series(new SeriesStyle(role: SeriesRole::Value)),
            'filled' => $this->series(new SeriesStyle(role: SeriesRole::In, fill: true, maxFill: true)),
            'stacked' => $this->series(new SeriesStyle(role: SeriesRole::Used, stacked: true)),
            'ranged' => $this->series(new SeriesStyle(role: SeriesRole::Value, range: true)),
        ], $theme);

        $this->assertNull($styles['line']->fillColor);
        $this->assertNull($styles['line']->maxFillColor);
        $this->assertNull($styles['line']->rangeColor);
        $this->assertSame($theme->fill(SeriesRole::In, '608720'), $styles['filled']->fillColor);
        $this->assertSame($theme->maxFill(SeriesRole::In, '608720'), $styles['filled']->maxFillColor);
        $this->assertSame('CC0000', $styles['stacked']->fillColor, 'a stacked area is the series color');
        $this->assertSame($theme->color(SeriesRole::Muted), $styles['ranged']->rangeColor);
    }

    public function test_stack_averages_share_the_draw_scale(): void
    {
        $averaged = new SeriesStyle(stacked: true, stackAverage: true);

        $styles = ResolvedStyle::resolve(['a' => $this->series($averaged), 'b' => $this->series($averaged), 'c' => $this->series()], new GraphTheme(false));

        $this->assertSame(0.5, $styles['a']->drawScale);
        $this->assertSame(0.5, $styles['b']->drawScale);
        $this->assertSame(1.0, $styles['c']->drawScale);
    }

    public function test_inverted_series_are_not_inverted_when_graphs_stack(): void
    {
        $series = ['out' => $this->series(new SeriesStyle(invert: true))];

        $this->assertTrue(ResolvedStyle::resolve($series, new GraphTheme(false))['out']->invert);

        $this->assertFalse(ResolvedStyle::resolve($series, new GraphTheme(false), inverts: false)['out']->invert);
    }

    private function series(SeriesStyle $style = new SeriesStyle): DataSeries
    {
        return new DataSeries(new MetricIdentity('test', ['hostname' => 'localhost']), 'value', 'Test', style: $style);
    }
}
