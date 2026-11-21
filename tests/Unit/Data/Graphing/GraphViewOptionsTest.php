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
            'line_style' => 'smooth',
            'columns' => ['p95', 'avg'],
            'width' => 0.5,
            'height' => 400,
        ]);

        $this->assertFalse($options->range);
        $this->assertSame('smooth', $options->line_style);
        $this->assertSame(['avg', 'p95'], $options->columns, 'in legend order');
        $this->assertSame(0.5, $options->width);
        $this->assertSame(400, $options->height);
    }

    public function test_how_one_graph_is_shown_is_not_a_preference(): void
    {
        $options = GraphViewOptions::fromPreference(['mirror' => false, 'stacking' => 'stack', 'scale' => 'log']);

        $this->assertTrue($options->mirror);
        $this->assertSame('graph', $options->stacking);
        $this->assertSame('graph', $options->scale);
    }

    public function test_unknown_values_are_the_defaults(): void
    {
        $options = GraphViewOptions::fromPreference([
            'range' => 'yes',
            'line_style' => 'dashed',
            'columns' => ['median', 'max'],
        ]);

        $this->assertTrue($options->range);
        $this->assertSame('stepped', $options->line_style);
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

    public function test_the_address_shows_one_graph_with_the_viewer_preferences(): void
    {
        $saved = new GraphViewOptions(range: false, columns: ['avg'], width: 0.5, height: 300, line_style: 'linear');

        $shared = $saved->withAddress(['mirror' => '0', 'stacking' => 'stack', 'line_style' => 'smooth', 'range' => '1']);

        $this->assertFalse($shared->mirror);
        $this->assertSame('stack', $shared->stacking);
        $this->assertSame('graph', $shared->scale, 'left out is the default');
        $this->assertFalse($shared->range, 'the shading is the viewer\'s');
        $this->assertSame('linear', $shared->line_style, 'the line style is the viewer\'s');
        $this->assertSame(['avg'], $shared->columns, 'the legend is the viewer\'s');
        $this->assertSame(300, $shared->height, 'the size is the viewer\'s');
    }

    public function test_an_address_without_view_options_is_the_graph_as_it_defines(): void
    {
        $options = new GraphViewOptions(columns: ['avg'])->withAddress(['type' => 'port_bits', 'scale' => 'cubic']);

        $this->assertEquals(new GraphViewOptions(columns: ['avg']), $options);
    }

    public function test_the_defaults_are_split_between_the_address_and_the_preferences(): void
    {
        $this->assertSame(['mirror' => true, 'stacking' => 'graph', 'scale' => 'graph'], GraphViewOptions::addressDefaults());
        $this->assertEqualsCanonicalizing(GraphViewOptions::PREFERENCES, array_keys(GraphViewOptions::preferenceDefaults()));
        $this->assertEqualsCanonicalizing(
            array_keys(get_object_vars(new GraphViewOptions)),
            [...GraphViewOptions::ADDRESS, ...GraphViewOptions::PREFERENCES],
            'every option is either',
        );
    }
}
