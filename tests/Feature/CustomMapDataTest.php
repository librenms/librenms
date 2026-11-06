<?php

namespace LibreNMS\Tests\Feature;

use App\Models\CustomMap;
use App\Models\CustomMapEdge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class CustomMapDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_edge_waypoints_and_arrow_style_round_trip(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $map = $this->createMap();

        $this->actingAs($user)
            ->postJson(route('maps.custom.data.save', ['map' => $map->custom_map_id]), $this->payload([
                'waypoints' => ['from' => [[10.4, 20], ['30', '40']], 'to' => []],
                'arrow_type' => 'vee',
                'arrow_scale' => 1.5,
            ]))
            ->assertOk();

        $edge = CustomMapEdge::firstOrFail();
        // the rest of the edge payload must survive the nested edges.* validation rules
        $this->assertEquals('dynamic', $edge->style);
        $this->assertNotNull($edge->custom_map_node1_id);
        $this->assertSame(['from' => [[10, 20], [30, 40]]], $edge->waypoints);
        $this->assertSame('vee', $edge->arrow_type);
        $this->assertSame(1.5, $edge->arrow_scale);

        $this->actingAs($user)
            ->getJson(route('maps.custom.data', ['map' => $map->custom_map_id]))
            ->assertOk()
            ->assertJsonPath("edges.$edge->custom_map_edge_id.waypoints.from.1", [30, 40])
            ->assertJsonPath("edges.$edge->custom_map_edge_id.arrow_type", 'vee')
            ->assertJsonPath("edges.$edge->custom_map_edge_id.arrow_scale", 1.5);
    }

    public function test_edge_without_waypoints_uses_defaults(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $map = $this->createMap();

        $this->actingAs($user)
            ->postJson(route('maps.custom.data.save', ['map' => $map->custom_map_id]), $this->payload([]))
            ->assertOk();

        $edge = CustomMapEdge::firstOrFail();
        $this->assertNull($edge->waypoints);
        $this->assertSame('arrow', $edge->arrow_type);
        $this->assertSame(0.6, $edge->arrow_scale);
    }

    public function test_invalid_edge_style_is_rejected(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $map = $this->createMap();

        $this->actingAs($user)
            ->postJson(route('maps.custom.data.save', ['map' => $map->custom_map_id]), $this->payload([
                'arrow_type' => 'bogus',
                'arrow_scale' => 50,
                'waypoints' => ['from' => [[1, 2, 3]]],
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['edges.new0.arrow_type', 'edges.new0.arrow_scale', 'edges.new0.waypoints.from.0']);

        $this->assertSame(0, CustomMapEdge::count());
    }

    private function createMap(): CustomMap
    {
        $map = new CustomMap;
        $map->name = 'Test map';
        $map->width = '800px';
        $map->height = '600px';
        $map->options = [];
        $map->newnodeconfig = [];
        $map->newedgeconfig = [];
        $map->save();

        return $map;
    }

    /**
     * @param  array<string, mixed>  $edge
     * @return array<string, mixed>
     */
    private function payload(array $edge): array
    {
        $node = fn (int $x) => [
            'device_id' => null,
            'linked_map_id' => null,
            'label' => "node $x",
            'shape' => 'box',
            'icon' => null,
            'size' => 25,
            'font' => ['face' => 'arial', 'size' => 14, 'color' => '#000000'],
            'color' => ['background' => '#ffffff', 'border' => '#000000'],
            'borderWidth' => 1,
            'x' => $x,
            'y' => 100,
        ];

        return [
            'newnodeconf' => [],
            'newedgeconf' => [],
            'nodes' => ['new0' => $node(100), 'new1' => $node(300)],
            'edges' => ['new0' => array_merge([
                'from' => 'new0',
                'to' => 'new1',
                'port_id' => null,
                'reverse' => false,
                'showpct' => false,
                'showbps' => false,
                'label' => '',
                'fixed_width' => null,
                'style' => 'dynamic',
                'text_face' => 'arial',
                'text_size' => 14,
                'text_colour' => '#000000',
                'text_align' => 'horizontal',
                'mid_x' => 200,
                'mid_y' => 100,
            ], $edge)],
            'legend_x' => -1,
            'legend_y' => -1,
            'legend_steps' => 7,
            'legend_font_size' => 14,
            'legend_hide_invalid' => false,
            'legend_hide_overspeed' => false,
            'legend_colours' => null,
        ];
    }
}
