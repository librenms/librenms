<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class PortsSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_ports(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create([
            'device_id' => $device->device_id,
            'ifDescr' => 'GigabitEthernet1/1',
            'ifAlias' => 'Test Port',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.ports', ['search' => 'Gigabit']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'ports')
            ->assertJsonPath('groups.0.results.0.name', 'GigabitEthernet1/1');
    }

    public function test_search_includes_mac_match(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create([
            'device_id' => $device->device_id,
            'ifPhysAddress' => '00:11:22:33:44:55',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.ports', ['search' => '00:11:22:33:44:55']));

        $response->assertStatus(200);
        $this->assertEquals($port->getLabel(), $response->json('groups.0.results.0.name'));
    }
}
