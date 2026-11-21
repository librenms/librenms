<?php

namespace LibreNMS\Tests\Unit\Graphs\Rrd;

use App\Data\Graphing\SeriesRole;
use App\Facades\LibrenmsConfig;
use App\Graphs\Rrd\GraphTheme;
use LibreNMS\Tests\TestCase;

class GraphThemeTest extends TestCase
{
    public function test_stacked_traffic_follows_the_configured_palettes(): void
    {
        LibrenmsConfig::set('graph_colours.purples', ['0000AA', '0000BB']);
        $theme = new GraphTheme(dark: false);

        $this->assertSame(['0000AA', '0000BB', '0000AA'], [
            $theme->next(SeriesRole::Out, stacked: true),
            $theme->next(SeriesRole::Out, stacked: true),
            $theme->next(SeriesRole::Out, stacked: true),
        ]);
    }

    public function test_series_without_a_role_use_the_palette_of_the_graph(): void
    {
        LibrenmsConfig::set('graph_colours.oranges', ['E43C00', 'E74B00']);
        $theme = new GraphTheme(dark: false, palette: 'oranges');

        $this->assertSame('E43C00', $theme->next(null));
        $this->assertSame('E74B00', $theme->next(null));
        $this->assertSame('E74B0020', $theme->fill(null, 'E74B00'));
    }

    public function test_roles_have_a_color_for_each_style(): void
    {
        $this->assertSame('272B30', new GraphTheme(dark: false)->next(SeriesRole::Value));
        $this->assertSame('F2F2F2', new GraphTheme(dark: true)->next(SeriesRole::Value));
        $this->assertSame('000000', new GraphTheme(dark: false)->color(SeriesRole::PortSpeed));
        $this->assertSame('F2F2F2', new GraphTheme(dark: true)->color(SeriesRole::PortSpeed));
    }

    public function test_translucent_themes_only_change_areas_and_fills(): void
    {
        $theme = new GraphTheme(dark: false, translucent: true);

        $this->assertSame('608720', $theme->next(SeriesRole::In));
        $this->assertSame('90B04088', $theme->fill(SeriesRole::In, '608720'));
        $this->assertSame('D7FFC788', $theme->maxFill(SeriesRole::In, '608720'));
        $this->assertSame('FF330088', $theme->next(SeriesRole::Error, stacked: true));
    }

    public function test_usage_roles_follow_the_used_percentage(): void
    {
        $this->assertSame(SeriesRole::UsageLow, SeriesRole::usage(10));
        $this->assertSame(SeriesRole::UsageModerate, SeriesRole::usage(30));
        $this->assertSame(SeriesRole::UsageHigh, SeriesRole::usage(60));
        $this->assertSame(SeriesRole::UsageVeryHigh, SeriesRole::usage(80, 90));
        $this->assertSame(SeriesRole::UsageCritical, SeriesRole::usage(80));
        $this->assertSame(SeriesRole::UsageCritical, SeriesRole::usage(45, 40));
    }
}
