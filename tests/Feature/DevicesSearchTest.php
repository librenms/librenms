<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class DevicesSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_devices(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create(['hostname' => 'test-device.example.com']);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.devices', ['search' => 'test-device']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'devices')
            ->assertJsonPath('groups.0.results.0.name', 'test-device.example.com');
    }

    public function test_search_respects_permissions(): void
    {
        $user = User::factory()->create(['enabled' => true]); // Normal user, no roles
        $device = Device::factory()->create(['hostname' => 'test-device.example.com']);

        // No permission given yet

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.devices', ['search' => 'test-device']));

        $response->assertStatus(200)
            ->assertJson(['groups' => []]);
    }

    public function test_search_includes_location_match(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create([
            'hostname' => 'other',
            'location_id' => \App\Models\Location::factory()->create(['location' => 'London'])->id,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.devices', ['search' => 'London']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'devices')
            ->assertJsonPath('groups.0.results.0.name', 'other');
    }

    public function test_search_includes_serial_match(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create([
            'hostname' => 'other',
            'serial' => 'SN12345',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.devices', ['search' => 'SN12345']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'devices')
            ->assertJsonPath('groups.0.results.0.name', 'other');
    }

    public function test_search_excludes_fdb_match(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create(['hostname' => 'switch']);
        $mac = '001122334455';

        // Create an FDB entry for this device
        \App\Models\PortsFdb::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => \App\Models\Port::factory()->create(['device_id' => $device->device_id])->port_id,
            'mac_address' => $mac,
            'vlan_id' => 1,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.devices', ['search' => '00:11:22:33:44:55']));

        $response->assertStatus(200)
            ->assertJson(['groups' => []]);
    }
}
