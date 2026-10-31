<?php

namespace LibreNMS\Tests\Feature\View\Components;

use Illuminate\Support\Facades\Blade;
use LibreNMS\Tests\TestCase;

class GraphTest extends TestCase
{
    public function testPopupGraphForwardsTypeAndVariables(): void
    {
        $html = Blade::render(
            <<<'BLADE'
                <x-graph type="device_bits" :vars="['device' => 42]" popup popup-title="Traffic" />
            BLADE
        );

        $this->assertStringContainsString('type=device_bits', $html);
        $this->assertStringContainsString('device=42', $html);
        $this->assertStringNotContainsString('type=&', $html);
        $this->assertGreaterThanOrEqual(5, substr_count($html, 'type=device_bits'));
    }

    public function testFullWidthPopupGraphUsesBlockWidthWrappers(): void
    {
        $html = Blade::render(
            <<<'BLADE'
                <x-graph type="device_processor" :vars="['device' => 42]" popup class="tw:w-full" img-class="tw:w-full tw:h-auto" />
            BLADE
        );

        $this->assertMatchesRegularExpression('/<a[^>]+class="[^"]*tw:w-full[^"]*tw:inline-block|<a[^>]+class="[^"]*tw:inline-block[^"]*tw:w-full/', $html);
        $this->assertMatchesRegularExpression('/<span[^>]+class="[^"]*tw:block[^"]*tw:w-full/', $html);
        $this->assertStringContainsString('tw:max-w-[calc(100vw-1rem)]', $html);
        $this->assertStringContainsString('tw:z-9999', $html);
    }

    public function testWideGraphUsesTheSessionViewportToSetItsDimensions(): void
    {
        session()->forget('screen_width');

        $fallbackHtml = Blade::render('<x-graph type="device_bits" aspect="wide" />');

        $this->assertStringContainsString('width="340" height="100"', $fallbackHtml);

        session(['screen_width' => 1200]);

        $defaultHtml = Blade::render('<x-graph type="device_bits" aspect="wide" />');

        $this->assertStringContainsString('width="340" height="100"', $defaultHtml);

        $desktopHtml = Blade::render("<x-graph type=\"device_bits\" aspect=\"wide\" :columns=\"['md' => 2]\" />");

        $this->assertStringContainsString('width="600" height="176"', $desktopHtml);

        $normalDesktopHtml = Blade::render("<x-graph type=\"device_bits\" :columns=\"['md' => 2]\" />");

        $this->assertStringContainsString('width="600" height="300"', $normalDesktopHtml);

        $explicitDimensionsHtml = Blade::render('<x-graph type="device_bits" aspect="wide" width="500" height="125" />');

        $this->assertStringContainsString('width="500" height="125"', $explicitDimensionsHtml);

        $explicitWidthHtml = Blade::render('<x-graph type="device_bits" aspect="wide" width="510" />');

        $this->assertStringContainsString('width="510" height="150"', $explicitWidthHtml);

        session(['screen_width' => 767]);

        $mobileHtml = Blade::render("<x-graph type=\"device_bits\" aspect=\"wide\" :columns=\"['md' => 2]\" />");

        $this->assertStringContainsString('width="767" height="226"', $mobileHtml);

        session(['screen_width' => 1280]);

        $multiBreakpointHtml = Blade::render("<x-graph type=\"device_bits\" aspect=\"wide\" :columns=\"['lg' => 3, 'sm' => 2]\" />");

        $this->assertStringContainsString('width="426" height="125"', $multiBreakpointHtml);
    }

    public function testResponsiveGraphRowPassesItsGridColumnsToChildGraphs(): void
    {
        session(['screen_width' => 900]);

        $html = Blade::render('<x-graph-row type="device_bits" columns="responsive" :graphs="[[\'from\' => \'-1d\']]" />');

        $this->assertStringContainsString('width="450" height="225"', $html);
    }
}
