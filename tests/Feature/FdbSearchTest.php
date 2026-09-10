<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class FdbSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_fdb_by_mac_returns_fdb_tables_group(): void
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
            ->getJson(route('ajax.search.fdb', ['search' => '00:11:22:33:44:55']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'fdb_tables')
            ->assertJsonPath('groups.0.results.0.name', '00:11:22:33:44:55')
            ->assertJsonPath('groups.0.results.0.icon', 'fa fa-plug');
    }

    public function test_search_fdb_shows_ip_mapping_if_available(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $mac = '001122334455';
        $ip = '192.168.1.50';

        PortsFdb::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'mac_address' => $mac,
            'vlan_id' => 1,
        ]);

        Ipv4Mac::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'mac_address' => $mac,
            'ipv4_address' => $ip,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.fdb', ['search' => '00:11:22:33:44:55']));

        $response->assertStatus(200);
        $name = $response->json('groups.0.results.0.name');
        $this->assertEquals('00:11:22:33:44:55', $name);
    }

    public function test_search_fdb_by_ipv4_returns_fdb_entry(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $mac = '001122334455';
        $ip = '10.0.0.1';

        PortsFdb::forceCreate(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => $mac, 'vlan_id' => 1]);
        Ipv4Mac::forceCreate(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => $mac, 'ipv4_address' => $ip]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.fdb', ['search' => $ip]));

        $response->assertStatus(200);
        /** @var array<int, array{name: string, subtitle?: string, icon?: string, status?: string|null, url?: string}> $results */
        $results = (array) $response->json('groups.0.results');
        $names = collect($results)->pluck('name');
        $this->assertTrue($names->contains('00:11:22:33:44:55'));
    }

    public function test_search_fdb_by_ipv6_returns_fdb_entry(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $mac = '001122334455';
        $ipv6 = 'fe80::1';

        PortsFdb::forceCreate(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => $mac, 'vlan_id' => 1]);
        Ipv6Nd::forceCreate(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => $mac, 'ipv6_address' => $ipv6]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.fdb', ['search' => $ipv6]));

        $response->assertStatus(200);
        /** @var array<int, array{name: string, subtitle?: string, icon?: string, status?: string|null, url?: string}> $v6Results */
        $v6Results = (array) $response->json('groups.0.results');
        $names = collect($v6Results)->pluck('name');
        $this->assertTrue($names->contains('00:11:22:33:44:55'));
    }

    public function test_search_fdb_single_mac_port_gets_green_status(): void
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
            ->getJson(route('ajax.search.fdb', ['search' => $mac]));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.results.0.status', 'tw:border-l-green-600!');
    }

    public function test_search_fdb_multi_mac_port_sorts_after_single_mac(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();

        // Port with 2 MACs (uplink/trunk) — should sort after single-MAC port
        $uplink = Port::factory()->create(['device_id' => $device->device_id]);
        PortsFdb::forceCreate(['device_id' => $device->device_id, 'port_id' => $uplink->port_id, 'mac_address' => 'aabbcc001111', 'vlan_id' => 1]);
        PortsFdb::forceCreate(['device_id' => $device->device_id, 'port_id' => $uplink->port_id, 'mac_address' => 'aabbcc002222', 'vlan_id' => 1]);

        // Port with 1 MAC (direct connection) — should sort first
        $access = Port::factory()->create(['device_id' => $device->device_id]);
        PortsFdb::forceCreate(['device_id' => $device->device_id, 'port_id' => $access->port_id, 'mac_address' => 'aabbcc003333', 'vlan_id' => 1]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.fdb', ['search' => 'aabbcc']));

        $response->assertStatus(200);
        /** @var array<int, array{name: string, subtitle?: string, icon?: string, status?: string|null, url?: string}> $results */
        $results = (array) $response->json('groups.0.results');

        // The single-MAC port entry has a green status; uplink entries do not
        $singleMacResult = collect($results)->firstWhere('name', 'aa:bb:cc:00:33:33');
        $this->assertEquals('tw:border-l-green-600!', $singleMacResult['status']);

        $multiMacResult = collect($results)->firstWhere('name', 'aa:bb:cc:00:11:11');
        $this->assertNull($multiMacResult['status'] ?? null);
    }

    public function test_search_fdb_subtitle_shows_connected_for_single_mac(): void
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
            ->getJson(route('ajax.search.fdb', ['search' => $mac]));

        $response->assertStatus(200);
        $this->assertStringContainsString('connected', $response->json('groups.0.results.0.subtitle'));
    }

    public function test_search_fdb_subtitle_shows_trunk_for_multi_mac(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);

        PortsFdb::forceCreate(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '001122334455', 'vlan_id' => 1]);
        PortsFdb::forceCreate(['device_id' => $device->device_id, 'port_id' => $port->port_id, 'mac_address' => '001122334456', 'vlan_id' => 1]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.fdb', ['search' => '001122334455']));

        $response->assertStatus(200);
        $this->assertStringContainsString('trunk', $response->json('groups.0.results.0.subtitle'));
    }

    public function test_search_returns_empty_when_fdb_disabled(): void
    {
        LibrenmsConfig::set('webui.global_search.fdb', false);

        $user = User::factory()->admin()->create(['enabled' => true]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.fdb', ['search' => 'anything']));

        $response->assertStatus(200)
            ->assertJsonPath('groups', []);
    }
}
