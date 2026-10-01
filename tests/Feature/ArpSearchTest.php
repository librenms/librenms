<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LibreNMS\Tests\TestCase;

class ArpSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_arp_by_ip_shows_ip_to_mac_binding(): void
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
            ->getJson(route('ajax.search.arp', ['search' => $ip]));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'arp_tables');

        $name = $response->json('groups.0.results.0.name');
        $this->assertStringContainsString($ip, $name);
        $this->assertStringContainsString('00:11:22:33:44:55', $name);
    }

    public function test_search_arp_by_mac_returns_arp_tables_group(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $ip = '192.168.1.2';
        $mac = '001122334455';

        Ipv4Mac::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'mac_address' => $mac,
            'ipv4_address' => $ip,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.arp', ['search' => '00:11:22:33:44:55']));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'arp_tables');

        $name = $response->json('groups.0.results.0.name');
        $this->assertStringContainsString($ip, $name);
        $this->assertStringContainsString('00:11:22:33:44:55', $name);
    }

    public function test_search_ndp_by_ipv6_shows_ip_to_mac_binding(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id]);
        $ipv6 = 'fe80::1';
        $mac = '001122334455';

        Ipv6Nd::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'mac_address' => $mac,
            'ipv6_address' => $ipv6,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.arp', ['search' => $ipv6]));

        $response->assertStatus(200)
            ->assertJsonPath('groups.0.type', 'arp_tables');

        $url = $response->json('groups.0.results.0.url');
        $this->assertStringContainsString('/device/' . $device->device_id . '/ports/view=nd', $url);
    }

    public function test_search_arp_subtitle_shows_device_and_port(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device = Device::factory()->create();
        $port = Port::factory()->create(['device_id' => $device->device_id, 'ifDescr' => 'GigabitEthernet0/0']);

        Ipv4Mac::forceCreate([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'mac_address' => '001122334455',
            'ipv4_address' => '10.0.0.1',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.arp', ['search' => '10.0.0.1']));

        $response->assertStatus(200);
        $subtitle = $response->json('groups.0.results.0.subtitle');
        $deviceDisplay = $device->refresh()->display ?: $device->hostname;
        $this->assertStringContainsString($deviceDisplay, $subtitle);
        $this->assertStringContainsString('GigabitEthernet0/0', $subtitle);
    }

    public function test_search_arp_summarizes_multiple_locations(): void
    {
        $user = User::factory()->admin()->create(['enabled' => true]);
        $device1 = Device::factory()->create();
        $device2 = Device::factory()->create();
        $port1 = Port::factory()->create(['device_id' => $device1->device_id]);
        $port2 = Port::factory()->create(['device_id' => $device2->device_id]);

        Ipv4Mac::forceCreate(['device_id' => $device1->device_id, 'port_id' => $port1->port_id, 'mac_address' => '001122334455', 'ipv4_address' => '10.0.0.1']);
        Ipv4Mac::forceCreate(['device_id' => $device2->device_id, 'port_id' => $port2->port_id, 'mac_address' => '001122334455', 'ipv4_address' => '10.0.0.1']);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.arp', ['search' => '10.0.0.1']));

        $response->assertStatus(200);
        $results = $response->json('groups.0.results');
        $this->assertCount(1, $results);
        $this->assertStringContainsString('Seen on 2 device(s) / 2 port(s)', $results[0]['subtitle']);
    }

    public function test_search_returns_empty_when_arp_disabled(): void
    {
        \LibrenmsConfig::set('librenms.webui.global_search.arp', false);

        $user = User::factory()->admin()->create(['enabled' => true]);

        $response = $this->actingAs($user)
            ->getJson(route('ajax.search.arp', ['search' => 'anything']));

        $response->assertStatus(200)
            ->assertJsonPath('groups', []);
    }
}
