<?php

namespace LibreNMS\Tests\Feature;

use App\Data\Graphing\GraphPageContext;
use App\Data\Graphing\GraphParameters;
use App\Data\Graphing\Legacy\LegacyAuthResult;
use App\Facades\Rrd;
use App\Graphs\Legacy\LegacyGraphScript;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\NoGraphData;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Graph;

class GraphErrorImageTest extends TestCase
{
    public function test_dark_graphs_use_white_for_the_no_data_message_and_title(): void
    {
        $params = new GraphParameters(['graph_type' => 'svg', 'style' => 'dark', 'graph_title' => 'Traffic']);

        $image = Graph::errorImage($params, new NoGraphData);

        $this->assertSame(2, substr_count($image->data, 'color:rgb(255, 255, 255)'));
    }

    public function test_error_graphs_follow_the_session_theme_unless_a_style_is_requested(): void
    {
        session(['applied_site_style' => 'dark']);

        $dark = Graph::errorImage(new GraphParameters(['graph_type' => 'svg']), new NoGraphData);
        $light = Graph::errorImage(new GraphParameters(['graph_type' => 'svg', 'style' => 'light']), new NoGraphData);

        $this->assertStringContainsString('color:rgb(255, 255, 255)', $dark->data);
        $this->assertStringContainsString('color:rgb(0, 0, 0)', $light->data);
    }

    public function test_dark_png_error_graphs_use_white_text(): void
    {
        $params = new GraphParameters(['graph_type' => 'png', 'style' => 'dark', 'graph_title' => 'Traffic']);

        $image = imagecreatefromstring(Graph::errorImage($params, new NoGraphData)->data);
        $opaqueColors = [];
        for ($index = 0; $index < imagecolorstotal($image); $index++) {
            $color = imagecolorsforindex($image, $index);
            if ($color['alpha'] === 0) {
                $opaqueColors[] = $color;
            }
        }

        $this->assertNotEmpty($opaqueColors);
        foreach ($opaqueColors as $color) {
            $this->assertSame(['red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 0], $color);
        }
        imagedestroy($image);
    }

    public function test_legacy_script_errors_preserve_the_title_set_before_failure(): void
    {
        Rrd::partialMock()->shouldReceive('checkRrdExists')->once()->andReturn(false);
        $params = new GraphParameters(['type' => 'application_ntp-server_stats', 'title' => 'yes']);
        $auth = new LegacyAuthResult(true, new GraphPageContext, [
            'graph_title' => 'Router NTP',
            'device' => ['hostname' => 'router'],
            'app' => (object) ['app_id' => 1],
        ]);

        try {
            LegacyGraphScript::find($params->type, $params->subtype)->runGraph($auth, $params);
            $this->fail('Expected a graph exception');
        } catch (GraphException $e) {
            $this->assertSame('Router NTP', $e->graphTitle);
        }
    }

    public function test_svg_error_preserves_the_enabled_graph_title(): void
    {
        $error = new NoGraphData('No Data files');
        $error->graphTitle = 'Router::Traffic';
        $params = new GraphParameters(['graph_type' => 'svg', 'title' => 'yes']);

        $image = Graph::errorImage($params, $error);

        $this->assertSame('Router::Traffic', $image->title);
        $this->assertStringContainsString('Router::Traffic', $image->data);
        $this->assertStringContainsString('No Data files', $image->data);
        $this->assertStringContainsString('color:rgb(0, 0, 0)', $image->data);
    }

    public function test_error_omits_the_disabled_graph_title(): void
    {
        $error = new NoGraphData;
        $error->graphTitle = 'Router::Traffic';
        $params = new GraphParameters(['graph_type' => 'svg', 'title' => 'no']);

        $image = Graph::errorImage($params, $error);

        $this->assertSame('Error', $image->title);
        $this->assertStringNotContainsString('Router::Traffic', $image->data);
        $this->assertStringContainsString('No Data', $image->data);
    }

    public function test_custom_error_title_overrides_the_original_and_is_escaped(): void
    {
        $error = new NoGraphData;
        $error->graphTitle = 'Original title';
        $params = new GraphParameters(['graph_type' => 'svg', 'title' => 'no', 'graph_title' => '<script> & uplink']);

        $image = Graph::errorImage($params, $error);

        $this->assertSame('<script> & uplink', $image->title);
        $this->assertStringContainsString('&lt;script&gt; &amp; uplink', $image->data);
        $this->assertStringNotContainsString('<script>', $image->data);
        $this->assertStringNotContainsString('Original title', $image->data);
    }

    public function test_error_without_an_available_title_still_renders(): void
    {
        $params = new GraphParameters(['graph_type' => 'svg', 'title' => 'yes']);

        $image = Graph::errorImage($params, new NoGraphData);

        $this->assertSame('Error', $image->title);
        $this->assertStringContainsString('No Data', $image->data);
    }

    public function test_png_draws_the_title_above_the_error_message(): void
    {
        $data = Graph::error('No Data', null, 400, 150, format: ImageFormat::Png, title: 'Router::Traffic');

        $image = imagecreatefromstring($data);
        $titlePixels = 0;
        for ($y = 5; $y < 20; $y++) {
            for ($x = 0; $x < 400; $x++) {
                $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                $titlePixels += $color['alpha'] === 0 && $color['red'] === 0 && $color['green'] === 0 && $color['blue'] === 0 ? 1 : 0;
            }
        }

        $this->assertGreaterThan(0, $titlePixels);
        $this->assertSame(400, imagesx($image));
        $this->assertSame(150, imagesy($image));
        imagedestroy($image);
    }
}
