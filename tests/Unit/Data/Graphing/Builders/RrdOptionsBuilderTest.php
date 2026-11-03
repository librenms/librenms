<?php

namespace LibreNMS\Tests\Unit\Data\Graphing\Builders;

use App\Data\Graphing\Builders\RrdOptionsBuilder;
use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use App\Facades\LibrenmsConfig;
use LibreNMS\Tests\TestCase;

class RrdOptionsBuilderTest extends TestCase
{
    public function test_it_autoscales_without_limits(): void
    {
        $options = $this->scaleOptions(new GraphOptions, []);

        $this->assertSame(['--alt-autoscale-max', '--rigid', '--slope-mode'], $options);
    }

    public function test_it_uses_the_requested_scale_when_the_graph_sets_none(): void
    {
        $options = $this->scaleOptions(new GraphOptions, ['scale_min' => '5', 'scale_max' => '50']);

        $this->assertSame(['-l', '5', '-u', '50', '--slope-mode'], $options);
    }

    public function test_graph_scale_takes_precedence_over_the_request(): void
    {
        $options = $this->scaleOptions(new GraphOptions(scaleMin: 0, scaleMax: 100), ['scale_min' => '5']);

        $this->assertSame(['-l', '0', '-u', '100', '--slope-mode'], $options);
    }

    public function test_it_escapes_text_options(): void
    {
        $options = $this->scaleOptions(new GraphOptions(verticalLabel: "Bob's \"label\""), []);

        $this->assertContains('Bobs label', $options);
    }

    public function test_it_builds_the_exact_image_options(): void
    {
        LibrenmsConfig::set('mono_font', 'Mono');
        LibrenmsConfig::set('rrdgraph_def_text', '-c BACK#EEEEEE');
        LibrenmsConfig::set('rrdgraph_def_text_color', '#111111');

        $params = new GraphParameters([
            'from' => 100, 'to' => 200, 'width' => 300, 'height' => 90, 'graph_type' => 'svg', 'absolute' => 1,
            'legend' => 'no', 'graph_title' => "Bob's title", 'bg' => 'FFFFFF', 'font' => '222222', 'bbg' => '333333',
        ]);

        $this->assertSame([
            '--start', '100', '--end', '200', '--width', '300', '--height', '90',
            '--full-size-mode',
            '--imgformat=SVG', '-m', '0.75', '-R', 'light',
            '--font', 'TITLE:7:Mono', '--font', 'LEGEND:7:Mono', '--font', 'AXIS:6:Mono',
            '--font-render-mode', 'normal',
            '-c', 'FONT#222222', '-c', 'BACK#333333', '-c', 'CANVAS#FFFFFF',
            '--only-graph',
            '-g',
            '--title=Bobs title',
            '--alt-autoscale-max', '--rigid', '--slope-mode',
        ], (new RrdOptionsBuilder($params))->build('ignored', new GraphOptions));
    }

    public function test_it_builds_the_exact_graph_options(): void
    {
        $options = new GraphOptions(
            scaleMin: 1.5, scaleRigid: true, sloped: false, base: 1000, unitsExponent: 3, logarithmic: true, altYGrid: true,
            verticalLabel: 'v', leftAxisFormat: 'l', rightAxis: '1:0', rightAxisLabel: 'r', unitsLength: 5,
        );

        $this->assertSame([
            '-l', '1.5', '--rigid', '--alt-y-grid', '--base', '1000', '--units-exponent', '3', '--logarithmic',
            '--right-axis', '1:0', '--right-axis-label', 'r', '--left-axis-format', 'l',
            '--units-length', '5', '--vertical-label', 'v',
        ], $this->scaleOptions($options, []));
    }

    public function test_it_shortens_long_titles_to_fit_the_width(): void
    {
        $options = (new RrdOptionsBuilder(new GraphParameters(['width' => 400, 'title' => 'yes'])))->build(str_repeat('x', 100), new GraphOptions);

        $this->assertContains('--title=' . str_repeat('x', 65) . '...', $options);
    }

    public function test_graph_size_is_clamped(): void
    {
        $params = new GraphParameters(['width' => '200000', 'height' => [1]]);

        $this->assertSame(GraphParameters::MAX_SIZE, $params->width);
        $this->assertSame(intdiv(GraphParameters::MAX_SIZE, 3), $params->height);
    }

    /**
     * The options after the common image options, which end with the font render mode and colors
     *
     * @param  array<string, mixed>  $input
     * @return array<int, string>
     */
    private function scaleOptions(GraphOptions $options, array $input): array
    {
        $params = new GraphParameters(['height' => 50, 'legend' => 'yes', ...$input]);
        $all = (new RrdOptionsBuilder($params))->build('', $options);

        return array_slice($all, array_search('--only-graph', $all, true) + 1);
    }
}
