<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\GraphOptions;
use App\Data\Graphing\GraphParameters;
use LibreNMS\Tests\TestCase;

class GraphOptionsTest extends TestCase
{
    public function test_it_autoscales_without_limits(): void
    {
        $options = (new GraphOptions)->toRrdOptions(new GraphParameters([]));

        $this->assertContains('--alt-autoscale-max', $options);
        $this->assertContains('--rigid', $options);
        $this->assertNotContains('-l', $options);
    }

    public function test_it_uses_the_requested_scale_when_the_graph_sets_none(): void
    {
        $options = (new GraphOptions)->toRrdOptions(new GraphParameters(['scale_min' => '5', 'scale_max' => '50']));

        $this->assertEquals(['-l', 5.0, '-u', 50.0, '--slope-mode'], $options);
    }

    public function test_graph_scale_takes_precedence_over_the_request(): void
    {
        $options = (new GraphOptions(scaleMin: 0, scaleMax: 100))->toRrdOptions(new GraphParameters(['scale_min' => '5']));

        $this->assertEquals(['-l', 0.0, '-u', 100.0, '--slope-mode'], $options);
    }

    public function test_it_escapes_text_options(): void
    {
        $options = (new GraphOptions(verticalLabel: "Bob's \"label\""))->toRrdOptions(new GraphParameters([]));

        $this->assertContains('Bobs label', $options);
    }
}
