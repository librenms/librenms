<?php

namespace LibreNMS\Tests\Unit\Data\Graphing;

use App\Data\Graphing\GraphViewOptions;
use LibreNMS\Tests\TestCase;

class GraphViewOptionsTest extends TestCase
{
    public function test_no_preference_is_the_defaults(): void
    {
        $this->assertEquals(new GraphViewOptions, GraphViewOptions::fromPreference(null));
        $this->assertEquals(new GraphViewOptions, GraphViewOptions::fromPreference('js'));
    }

    public function test_a_stored_preference_is_kept(): void
    {
        $options = GraphViewOptions::fromPreference([
            'range' => false,
            'mirror' => false,
            'stacking' => 'stack',
            'scale' => 'log',
            'columns' => ['p95', 'avg'],
            'width' => 0.5,
            'height' => 400,
        ]);

        $this->assertFalse($options->range);
        $this->assertFalse($options->mirror);
        $this->assertSame('stack', $options->stacking);
        $this->assertSame('log', $options->scale);
        $this->assertSame(['avg', 'p95'], $options->columns, 'in legend order');
        $this->assertSame(0.5, $options->width);
        $this->assertSame(400, $options->height);
    }

    public function test_unknown_values_are_the_defaults(): void
    {
        $options = GraphViewOptions::fromPreference([
            'range' => 'yes',
            'mirror' => 1,
            'stacking' => 'sideways',
            'scale' => 'cubic',
            'columns' => ['median', 'max'],
        ]);

        $this->assertTrue($options->range);
        $this->assertTrue($options->mirror);
        $this->assertSame('graph', $options->stacking);
        $this->assertSame('graph', $options->scale);
        $this->assertSame(['max'], $options->columns);
    }

    public function test_a_size_needs_a_usable_height(): void
    {
        $this->assertNull(GraphViewOptions::fromPreference(['width' => 0.5, 'height' => 10])->height);
        $this->assertNull(GraphViewOptions::fromPreference(['width' => 0.5, 'height' => 10])->width);
        $this->assertNull(GraphViewOptions::fromPreference(['width' => 0.5])->width);

        $wide = GraphViewOptions::fromPreference(['width' => 3, 'height' => 300]);
        $this->assertSame(1.0, $wide->width);
        $this->assertSame(GraphViewOptions::MIN_WIDTH, GraphViewOptions::fromPreference(['width' => 0.01, 'height' => 300])->width);
    }

    public function test_an_address_without_drawing_options_keeps_the_saved_ones(): void
    {
        $saved = new GraphViewOptions(scale: 'log', columns: ['avg'], width: 0.5, height: 300);

        $this->assertSame($saved, $saved->withAddress(['type' => 'port_bits', 'from' => '-1d']));
    }

    public function test_an_address_with_drawing_options_describes_the_whole_drawing(): void
    {
        $saved = new GraphViewOptions(range: false, scale: 'log', columns: ['avg'], width: 0.5, height: 300);

        $shared = $saved->withAddress(['mirror' => '0', 'stacking' => 'stack']);

        $this->assertTrue($shared->range, 'left out is the default');
        $this->assertFalse($shared->mirror);
        $this->assertSame('stack', $shared->stacking);
        $this->assertSame('graph', $shared->scale, 'left out is the default');
        $this->assertSame(['avg'], $shared->columns, 'the legend is the viewer\'s');
        $this->assertSame(300, $shared->height, 'the size is the viewer\'s');
    }
}
