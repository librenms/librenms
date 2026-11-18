<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\DataSeries;
use App\Data\Graphing\ResolvedStyle;
use App\Data\Graphing\SeriesStyle;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;

class ResolvedStyleTest extends TestCase
{
    public function test_series_without_a_color_take_the_palette_in_order(): void
    {
        LibrenmsConfig::set('graph_colours.test', ['aa0000', 'bb0000']);

        $styles = ResolvedStyle::resolve([
            'a' => $this->series(),
            'b' => $this->series(new SeriesStyle(color: '00ff00')),
            'c' => $this->series(),
            'd' => $this->series(),
        ], 'test');

        $this->assertSame(['a' => 'aa0000', 'b' => '00ff00', 'c' => 'bb0000', 'd' => 'aa0000'], array_map(fn (ResolvedStyle $style) => $style->color, $styles));
    }

    public function test_fills(): void
    {
        $styles = ResolvedStyle::resolve([
            'line' => $this->series(new SeriesStyle(color: '111111')),
            'filled' => $this->series(new SeriesStyle(color: '222222', fill: true)),
            'colored' => $this->series(new SeriesStyle(color: '333333', fill: true, fillColor: '444444')),
            'stacked' => $this->series(new SeriesStyle(color: '555555', stacked: true)),
        ], 'mixed');

        $this->assertNull($styles['line']->fillColor);
        $this->assertSame('22222220', $styles['filled']->fillColor, 'a transparent version of the line');
        $this->assertSame('444444', $styles['colored']->fillColor);
        $this->assertSame('555555', $styles['stacked']->fillColor, 'a stacked area is the series color');
    }

    public function test_stack_averages_share_the_draw_scale(): void
    {
        $averaged = new SeriesStyle(stacked: true, stackAverage: true);

        $styles = ResolvedStyle::resolve(['a' => $this->series($averaged), 'b' => $this->series($averaged), 'c' => $this->series()], 'mixed');

        $this->assertSame(0.5, $styles['a']->drawScale);
        $this->assertSame(0.5, $styles['b']->drawScale);
        $this->assertSame(1.0, $styles['c']->drawScale);
    }

    public function test_inverted_series_are_not_inverted_when_graphs_stack(): void
    {
        $series = ['out' => $this->series(new SeriesStyle(invert: true))];

        LibrenmsConfig::set('webui.graph_stacked', false);
        $this->assertTrue(ResolvedStyle::resolve($series, 'mixed')['out']->invert);

        LibrenmsConfig::set('webui.graph_stacked', true);
        $this->assertFalse(ResolvedStyle::resolve($series, 'mixed')['out']->invert);
    }

    private function series(SeriesStyle $style = new SeriesStyle): DataSeries
    {
        return new DataSeries(new MetricIdentity('test', ['hostname' => 'localhost']), 'value', 'Test', style: $style);
    }
}
