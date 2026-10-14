<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Facades\LibrenmsConfig;
use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\Link;
use App\Models\Service;
use App\Models\User;
use App\Models\Vlan;
use App\Models\Vrf;
use App\Models\WirelessSensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

/**
 * An unknown hostname must not drop the device filter and return every device's rows,
 * and a user limited to some devices must only get those devices.
 */
final class UnknownDeviceFilterApiTest extends DBTestCase
{
    use DatabaseTransactions;

    private const UNKNOWN = 'does-not-exist.example.com';
    private const FORBIDDEN = 'Insufficient permissions to access this device';

    private Device $device;
    private Device $other;
    private string $unknownId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = Device::factory()->create();
        $this->other = Device::factory()->create();
        $this->unknownId = (string) ($this->other->device_id + 1000);
    }

    public function testListLinks(): void
    {
        Link::factory()->create(['local_device_id' => $this->device->device_id]);
        Link::factory()->create(['local_device_id' => $this->other->device_id]);
        $headers = $this->headers(User::factory()->admin()->create());

        foreach ([self::UNKNOWN, $this->unknownId] as $unknown) {
            $this->getJson("/api/v0/devices/$unknown/links", $headers)
                ->assertStatus(404)
                ->assertJsonPath('message', "Device $unknown does not exist");
        }

        foreach ([$this->device->hostname, $this->device->device_id] as $hostname) {
            $this->getJson("/api/v0/devices/$hostname/links", $headers)
                ->assertStatus(200)
                ->assertJsonPath('count', 1)
                ->assertJsonPath('links.0.local_device_id', $this->device->device_id);
        }

        $this->getJson('/api/v0/resources/links', $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 2);
    }

    public function testListLogs(): void
    {
        Eventlog::factory()->create(['device_id' => $this->device->device_id]);
        Eventlog::factory()->create(['device_id' => $this->other->device_id]);
        $headers = $this->headers(User::factory()->admin()->create());

        foreach (['eventlog', 'syslog', 'alertlog'] as $log) {
            foreach ([self::UNKNOWN, $this->unknownId] as $unknown) {
                $this->getJson("/api/v0/logs/$log/$unknown", $headers)
                    ->assertStatus(404)
                    ->assertJsonPath('message', "Device $unknown does not exist");
            }
        }

        // creating a device logs an event too, so compare with the table
        $logs = $this->getJson("/api/v0/logs/eventlog/{$this->device->hostname}", $headers)
            ->assertStatus(200)
            ->assertJsonPath('total', Eventlog::where('device_id', $this->device->device_id)->count())
            ->json('logs');
        $this->assertSame([$this->device->device_id], array_values(array_unique(array_column($logs, 'device_id'))));

        $this->getJson('/api/v0/logs/eventlog', $headers)
            ->assertStatus(200)
            ->assertJsonPath('total', Eventlog::count());
    }

    public function testListBgp(): void
    {
        BgpPeer::factory()->create(['device_id' => $this->device->device_id]);
        BgpPeer::factory()->create(['device_id' => $this->other->device_id]);
        $headers = $this->headers(User::factory()->admin()->create());

        foreach ([self::UNKNOWN, $this->unknownId] as $unknown) {
            $this->getJson("/api/v0/bgp?hostname=$unknown", $headers)
                ->assertStatus(404)
                ->assertJsonPath('message', "Device $unknown does not exist");
        }

        foreach ([$this->device->hostname, $this->device->device_id] as $hostname) {
            $this->getJson("/api/v0/bgp?hostname=$hostname", $headers)
                ->assertStatus(200)
                ->assertJsonPath('count', 1)
                ->assertJsonPath('bgp_sessions.0.device_id', $this->device->device_id);
        }

        $this->getJson('/api/v0/bgp', $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 2);

        $this->getJson('/api/v0/bgp?hostname=', $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 2);
    }

    public function testListVrfAndVlans(): void
    {
        Vrf::factory()->create(['device_id' => $this->device->device_id, 'vrf_name' => 'shared']);
        Vrf::factory()->create(['device_id' => $this->other->device_id, 'vrf_name' => 'shared']);
        Vlan::factory()->create(['device_id' => $this->device->device_id]);
        Vlan::factory()->create(['device_id' => $this->other->device_id]);
        $headers = $this->headers(User::factory()->admin()->create());

        foreach (['/api/v0/routing/vrf', '/api/v0/resources/vlans', '/api/v0/routing/bgp/cbgp', '/api/v0/ospf', '/api/v0/ospfv3', '/api/v0/routing/mpls/services'] as $url) {
            $this->getJson("$url?hostname=" . self::UNKNOWN, $headers)
                ->assertStatus(404)
                ->assertJsonPath('message', 'Device ' . self::UNKNOWN . ' does not exist');
        }

        $this->getJson("/api/v0/routing/vrf?hostname={$this->device->hostname}", $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('vrfs.0.device_id', $this->device->device_id);

        // vrfname used to replace the device filter
        $this->getJson("/api/v0/routing/vrf?hostname={$this->device->hostname}&vrfname=shared", $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('vrfs.0.device_id', $this->device->device_id);

        $this->getJson('/api/v0/routing/vrf?vrfname=shared', $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 2);

        $this->getJson("/api/v0/resources/vlans?hostname={$this->device->device_id}", $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('vlans.0.device_id', $this->device->device_id);

        $this->getJson('/api/v0/resources/vlans', $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 2);
    }

    public function testDeviceEndpoints(): void
    {
        $headers = $this->headers(User::factory()->admin()->create());

        foreach ([self::UNKNOWN, $this->unknownId] as $unknown) {
            foreach (["/api/v0/devices/$unknown/vlans", "/api/v0/devices/$unknown/maintenance", "/api/v0/devices/$unknown/fdb", "/api/v0/routing/ipsec/data/$unknown", "/api/v0/services/$unknown"] as $url) {
                $this->getJson($url, $headers)
                    ->assertStatus(404)
                    ->assertJsonPath('message', "Device $unknown does not exist");
            }
        }

        $this->getJson("/api/v0/devices/{$this->device->device_id}/maintenance", $headers)
            ->assertStatus(200)
            ->assertJsonPath('is_under_maintenance', false);

        // a device without components used to hit an undefined array key
        $this->getJson("/api/v0/devices/{$this->device->hostname}/components", $headers)
            ->assertStatus(200)
            ->assertJsonPath('components', []);
    }

    public function testLimitedUserOnlySeesPermittedDevices(): void
    {
        BgpPeer::factory()->create(['device_id' => $this->device->device_id]);
        BgpPeer::factory()->create(['device_id' => $this->other->device_id]);
        Eventlog::factory()->create(['device_id' => $this->device->device_id]);
        Eventlog::factory()->create(['device_id' => $this->other->device_id]);
        Link::factory()->create(['local_device_id' => $this->device->device_id]);
        Link::factory()->create(['local_device_id' => $this->other->device_id]);
        Service::factory()->create(['device_id' => $this->device->device_id]);
        Service::factory()->create(['device_id' => $this->other->device_id]);
        $otherSensor = WirelessSensor::factory()->create(['device_id' => $this->other->device_id, 'sensor_class' => 'rssi']);

        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->devicesOwned()->attach($this->device->device_id);
        $headers = $this->headers($user);

        $this->getJson('/api/v0/bgp', $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('bgp_sessions.0.device_id', $this->device->device_id);

        $this->getJson("/api/v0/bgp?hostname={$this->device->hostname}", $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 1);

        $logs = $this->getJson('/api/v0/logs/eventlog', $headers)
            ->assertStatus(200)
            ->assertJsonPath('total', Eventlog::where('device_id', $this->device->device_id)->count())
            ->json('logs');
        $this->assertSame([$this->device->device_id], array_values(array_unique(array_column($logs, 'device_id'))));

        $this->getJson("/api/v0/devices/{$this->device->hostname}/links", $headers)
            ->assertStatus(200)
            ->assertJsonPath('count', 1);

        LibrenmsConfig::set('show_services', true);
        $this->getJson('/api/v0/services', $headers)
            ->assertStatus(200)
            ->assertJsonCount(1, 'services.0')
            ->assertJsonPath('services.0.0.device_id', $this->device->device_id);

        // a sensor id of another device must not be readable through a permitted device
        $this->getJson("/api/v0/devices/{$this->device->hostname}/wireless/device_wireless_rssi/$otherSensor->sensor_id", $headers)
            ->assertStatus(200)
            ->assertJsonPath('graphs', []);

        // a forbidden device and an unknown one get the same answer, so existence does not leak
        foreach ([$this->other->hostname, (string) $this->other->device_id, self::UNKNOWN, $this->unknownId] as $hostname) {
            foreach (["/api/v0/bgp?hostname=$hostname", "/api/v0/logs/eventlog/$hostname", "/api/v0/devices/$hostname/links", "/api/v0/routing/ipsec/data/$hostname"] as $url) {
                $this->getJson($url, $headers)
                    ->assertStatus(403)
                    ->assertJsonPath('message', self::FORBIDDEN);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function headers(User $user): array
    {
        return ['X-Auth-Token' => $user->createToken('test')->plainTextToken];
    }
}
