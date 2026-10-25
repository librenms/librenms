<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Facades\Permissions;
use App\Models\AlertFault;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\AuthLog;
use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\IpsecTunnel;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv4Network;
use App\Models\Link;
use App\Models\OspfNbr;
use App\Models\Port;
use App\Models\Service;
use App\Models\Syslog;
use App\Models\User;
use App\Models\Vlan;
use App\Models\Vrf;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;

final class ApiDevicePermissionsTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $allowed;
    private Device $denied;

    /** @var array<string, array{allowed: int|string, denied: int|string}> */
    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('show_services', true);

        $this->allowed = Device::factory()->create(['hostname' => 'allowed.example.test']);
        $this->denied = Device::factory()->create(['hostname' => 'denied.example.test']);

        $rule = AlertRule::factory()->create();

        foreach (['allowed' => $this->allowed, 'denied' => $this->denied] as $key => $device) {
            $octet = $key === 'allowed' ? 1 : 2;
            $port = Port::factory()->for($device)->create();
            $network = Ipv4Network::factory()->create(['ipv4_network' => "192.0.$octet.0/24"]);
            Ipv4Address::factory()->create([
                'port_id' => $port->port_id,
                'ipv4_network_id' => $network->ipv4_network_id,
                'ipv4_address' => "192.0.$octet.10",
                'ipv4_prefixlen' => 24,
            ]);
            Ipv4Mac::factory()->create([
                'port_id' => $port->port_id,
                'device_id' => $device->device_id,
                'ipv4_address' => "192.0.$octet.20",
            ]);

            $this->ids['network'][$key] = $network->ipv4_network_id;
            $this->ids['bgp'][$key] = BgpPeer::factory()->create(['device_id' => $device->device_id, 'bgpPeerState' => 'established'])->bgpPeer_id;
            $this->ids['vrf'][$key] = Vrf::factory()->create(['device_id' => $device->device_id])->vrf_id;
            $this->ids['link'][$key] = Link::factory()->create(['local_device_id' => $device->device_id, 'local_port_id' => $port->port_id, 'remote_device_id' => 0])->id;
            $this->ids['ipsec'][$key] = IpsecTunnel::factory()->create(['device_id' => $device->device_id])->tunnel_id;
            $this->ids['ospf'][$key] = OspfNbr::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id])->id;
            $this->ids['service'][$key] = Service::factory()->create(['device_id' => $device->device_id])->service_id;
            $this->ids['eventlog'][$key] = Eventlog::factory()->create(['device_id' => $device->device_id])->event_id;
            $this->ids['syslog'][$key] = Syslog::factory()->create(['device_id' => $device->device_id, 'facility' => 'daemon'])->seq;
            $this->ids['alertlog'][$key] = AlertLog::factory()->create(['device_id' => $device->device_id, 'rule_id' => $rule->id, 'details' => ['key' => $key]])->id;
            $this->ids['alert'][$key] = AlertFault::create([
                'rule_id' => $rule->id,
                'device_id' => $device->device_id,
                'entity_key' => (string) $device->device_id,
                'state' => AlertState::ACTIVE,
                'open' => 1,
                'alerted' => 1,
                'details' => ['key' => $key],
            ])->id;
            Vlan::factory()->create(['device_id' => $device->device_id]);
            BgpPeerCbgp::forceCreate(array_merge(
                ['device_id' => $device->device_id, 'bgpPeerIdentifier' => "192.0.$octet.30", 'afi' => 'ipv4', 'safi' => 'unicast'],
                array_fill_keys(['AcceptedPrefixes', 'DeniedPrefixes', 'PrefixAdminLimit', 'PrefixThreshold', 'PrefixClearThreshold', 'AdvertisedPrefixes', 'SuppressedPrefixes', 'WithdrawnPrefixes',
                    'AcceptedPrefixes_delta', 'AcceptedPrefixes_prev', 'DeniedPrefixes_delta', 'DeniedPrefixes_prev', 'AdvertisedPrefixes_delta', 'AdvertisedPrefixes_prev',
                    'SuppressedPrefixes_delta', 'SuppressedPrefixes_prev', 'WithdrawnPrefixes_delta', 'WithdrawnPrefixes_prev'], 0),
            ));
        }
    }

    public function testLimitedUserOnlySeesPermittedDevices(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->devicesOwned()->attach($this->allowed->device_id);
        Permissions::invalidateCache();
        $headers = ['X-Auth-Token' => $user->createToken('test')->plainTextToken];

        $this->assertListed($headers, '/api/v0/bgp', 'bgp_sessions.*.device_id');
        $this->assertListed($headers, '/api/v0/ospf', 'ospf_neighbours.*.device_id');
        $this->assertListed($headers, '/api/v0/services', 'services.0.*.device_id');
        $this->assertListed($headers, '/api/v0/logs/eventlog', 'logs.*.device_id');
        $this->assertListed($headers, '/api/v0/logs/syslog', 'logs.*.device_id');
        $this->assertListed($headers, '/api/v0/resources/ip/arp/all', 'arp.*.device_id');
        $this->assertListed($headers, '/api/v0/logs/alertlog', 'logs.*.device_id');
        $this->assertListed($headers, '/api/v0/alerts', 'alerts.*.device_id');
        $this->assertListed($headers, '/api/v0/routing/bgp/cbgp', 'bgp_counters.*.device_id');
        $this->assertListed($headers, '/api/v0/routing/vrf', 'vrfs.*.device_id');
        $this->assertListed($headers, '/api/v0/resources/links', 'links.*.local_device_id');

        $this->json('GET', '/api/v0/alerts', [], $headers)->assertJsonPath('alerts.0.details', ['key' => 'allowed']);
        $this->json('GET', '/api/v0/logs/alertlog', [], $headers)->assertJsonPath('logs.0.details', ['key' => 'allowed'])->assertJsonPath('total', 1);
        $this->json('GET', '/api/v0/logs/eventlog', [], $headers)->assertJsonPath('logs.0.host', $this->allowed->device_id);
        $this->json('GET', '/api/v0/logs/syslog?from=' . ($this->ids['syslog']['allowed'] + 1), [], $headers)->assertJsonPath('logs', [])->assertJsonPath('total', 0);
        $this->json('GET', "/api/v0/alerts/{$this->ids['alert']['denied']}", [], $headers)->assertJsonPath('alerts', []);

        $this->json('GET', "/api/v0/services/{$this->denied->device_id}", [], $headers)->assertJsonPath('services.0', []);
        $this->json('GET', "/api/v0/logs/eventlog/{$this->denied->device_id}", [], $headers)->assertJsonPath('logs', []);
        $this->json('GET', '/api/v0/resources/ip/arp/all?device=' . $this->denied->hostname, [], $headers)->assertJsonPath('arp', []);
        $this->json('GET', '/api/v0/resources/ip/arp/all?device=missing.example.test', [], $headers)->assertOk()->assertJsonPath('arp', []);

        $addresses = $this->json('GET', '/api/v0/resources/ip/addresses/ipv4', [], $headers)->assertOk()->json('ip_addresses.*.ipv4_address');
        $this->assertEquals(['192.0.1.10'], $addresses);
        $networks = $this->json('GET', '/api/v0/resources/ip/networks/ipv4', [], $headers)->assertOk()->json('ip_networks.*.ipv4_network_id');
        $this->assertEquals([$this->ids['network']['allowed']], $networks);
        $this->json('GET', "/api/v0/resources/ip/networks/{$this->ids['network']['allowed']}/ip", [], $headers)->assertOk();
        $this->json('GET', "/api/v0/resources/ip/networks/{$this->ids['network']['denied']}/ip", [], $headers)->assertNotFound();

        $this->json('GET', "/api/v0/bgp/{$this->ids['bgp']['allowed']}", [], $headers)->assertOk();
        $this->json('GET', "/api/v0/bgp/{$this->ids['bgp']['denied']}", [], $headers)->assertForbidden();
        $this->json('GET', "/api/v0/routing/vrf/{$this->ids['vrf']['allowed']}", [], $headers)->assertOk();
        $this->json('GET', "/api/v0/routing/vrf/{$this->ids['vrf']['denied']}", [], $headers)->assertForbidden();
        $this->json('GET', "/api/v0/resources/links/{$this->ids['link']['allowed']}", [], $headers)->assertOk();
        $this->json('GET', "/api/v0/resources/links/{$this->ids['link']['denied']}", [], $headers)->assertForbidden();
        $this->json('GET', "/api/v0/routing/ipsec/data/{$this->allowed->device_id}", [], $headers)->assertOk();
        $this->json('GET', "/api/v0/routing/ipsec/data/{$this->denied->device_id}", [], $headers)->assertForbidden();

        $this->json('GET', '/api/v0/logs/authlog', [], $headers)->assertForbidden();
    }

    public function testAdminSeesAllDevices(): void
    {
        AuthLog::factory()->create();
        $headers = ['X-Auth-Token' => User::factory()->admin()->create()->createToken('test')->plainTextToken];

        $devices = [$this->allowed->device_id, $this->denied->device_id];
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/bgp', [], $headers)->json('bgp_sessions.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/services', [], $headers)->json('services.0.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/resources/ip/arp/all', [], $headers)->json('arp.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/alerts', [], $headers)->json('alerts.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/routing/vrf', [], $headers)->json('vrfs.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/resources/vlans', [], $headers)->json('vlans.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/logs/syslog', [], $headers)->json('logs.*.device_id'));
        $this->assertEquals([$this->denied->device_id], $this->json('GET', "/api/v0/bgp?hostname={$this->denied->device_id}", [], $headers)->json('bgp_sessions.*.device_id'));
        $this->assertCount(1, $this->json('GET', '/api/v0/logs/eventlog?sortorder=DESC&limit=1', [], $headers)->json('logs'));
        $this->assertCount(2, $this->json('GET', '/api/v0/resources/ip/networks/ipv4', [], $headers)->json('ip_networks'));
        $this->assertCount(2, $this->json('GET', '/api/v0/resources/ip/addresses/ipv4', [], $headers)->json('ip_addresses'));
        $this->json('GET', "/api/v0/bgp/{$this->ids['bgp']['denied']}", [], $headers)->assertOk();
        $this->json('GET', '/api/v0/logs/authlog', [], $headers)->assertOk();
    }

    public function testModelViewAllPermissionSeesAllDevices(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->devicesOwned()->attach($this->allowed->device_id);
        foreach (['eventlog.viewAll', 'syslog.viewAll', 'alert-log.viewAll', 'routing.viewAll'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission));
        }
        Permissions::invalidateCache();
        $headers = ['X-Auth-Token' => $user->createToken('test')->plainTextToken];

        $devices = [$this->allowed->device_id, $this->denied->device_id];
        $this->assertEqualsCanonicalizing($devices, array_unique($this->json('GET', '/api/v0/logs/eventlog?limit=100', [], $headers)->json('logs.*.device_id')));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/logs/syslog', [], $headers)->json('logs.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/logs/alertlog', [], $headers)->json('logs.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/ospf', [], $headers)->json('ospf_neighbours.*.device_id'));
        $this->assertEqualsCanonicalizing($devices, $this->json('GET', '/api/v0/bgp', [], $headers)->json('bgp_sessions.*.device_id'));
        $this->assertEquals([$this->allowed->device_id], $this->json('GET', '/api/v0/services', [], $headers)->json('services.0.*.device_id'));
    }

    /**
     * Every assignable viewAll permission must be reachable through Gate so hasAccess scopes honor it.
     */
    public function testAssignableViewAllPermissionsHavePolicyAbility(): void
    {
        $models = [
            'alert-log.viewAll' => \App\Models\AlertLog::class,
            'application.viewAll' => \App\Models\Application::class,
            'eventlog.viewAll' => Eventlog::class,
            'inventory.viewAll' => \App\Models\EntPhysical::class,
            'outage.viewAll' => \App\Models\DeviceOutage::class,
            'routing.viewAll' => \App\Models\Route::class,
            'storage.viewAll' => \App\Models\Storage::class,
            'syslog.viewAll' => Syslog::class,
        ];

        foreach ($models as $permission => $model) {
            $user = User::factory()->create();
            $this->assertFalse($user->can('viewAll', $model), $permission);
            $user->givePermissionTo(Permission::findOrCreate($permission));
            $this->assertTrue($user->fresh()->can('viewAll', $model), $permission);
        }
    }

    public function testInvalidAlertLogDetailsDecodeToEmptyArray(): void
    {
        DB::table('alert_log')->where('id', $this->ids['alertlog']['allowed'])->update(['details' => 'not compressed']);
        $headers = ['X-Auth-Token' => User::factory()->admin()->create()->createToken('test')->plainTextToken];

        $this->json('GET', "/api/v0/logs/alertlog/{$this->allowed->device_id}", [], $headers)
            ->assertOk()
            ->assertJsonPath('logs.0.details', []);
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function assertListed(array $headers, string $uri, string $path): void
    {
        $device_ids = $this->json('GET', $uri, [], $headers)->assertOk()->json($path);
        $this->assertNotEmpty($device_ids, $uri);
        $this->assertEquals([$this->allowed->device_id], array_values(array_unique($device_ids)), $uri);
    }
}
