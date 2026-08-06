<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class EndpointsSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_fdb_match()
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $mac = '001122334455';

        PortsFdb::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'mac_address' => $mac,
            'vlan_id' => 1,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.endpoints', ['search' => '00:11:22:33:44:55']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'endpoints')
            ->assertJsonPath('groups.0.results.0.name', $mac);
        
        $this->assertStringContainsString('(FDB)', $response->json('groups.0.results.0.subtitle'));
    }

    public function test_search_returns_arp_match()
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $ip = '192.168.1.1';
        $mac = '001122334455';

        Ipv4Mac::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'mac_address' => $mac,
            'ipv4_address' => $ip,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.endpoints', ['search' => '192.168.1.1']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.results.0.name', $ip);
            
        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.endpoints', ['search' => '00:11:22:33:44:55']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.results.0.name', $ip);
    }

    public function test_search_returns_ndp_match()
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $ip = 'fe80::1';
        $mac = '001122334455';

        Ipv6Nd::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'mac_address' => $mac,
            'ipv6_address' => $ip,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.endpoints', ['search' => 'fe80::1']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.results.0.name', $ip);
    }
}
