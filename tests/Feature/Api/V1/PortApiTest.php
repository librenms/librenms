<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;

final class PortApiTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('api.v1.enabled', true);
    }

    private function apiUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo(Permission::findOrCreate('api.access'));

        return $user;
    }

    public function testShowIsAJsonApiDocument(): void
    {
        $port = Port::factory()->for(Device::factory())->create(['ifName' => 'eth0', 'ifOperStatus' => 'up', 'deleted' => 0]);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson("/api/v1/ports/$port->port_id")
            ->assertOk()
            ->assertJsonPath('data.id', (string) $port->port_id)
            ->assertJsonPath('data.type', 'ports')
            ->assertJsonPath('data.attributes.name', 'eth0')
            ->assertJsonPath('data.attributes.operationalStatus', 'up')
            ->assertJsonPath('data.attributes.isDeleted', false)
            ->assertJsonMissingPath('data.attributes.ifName')
            ->assertJsonMissingPath('data.attributes.deviceId')
            ->assertJsonPath('data.relationships.device.links.related', route('api.v1.ports.device', $port->port_id))
            ->assertJsonMissingPath('data.relationships.device.data')
            ->assertJsonMissingPath('included');
    }

    public function testIncludeDevice(): void
    {
        $device = Device::factory()->create();
        Port::factory()->count(2)->for($device)->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->getJson("/api/v1/ports?filter[device]=$device->device_id&include=device")
            ->assertOk()
            ->assertJsonPath('data.0.relationships.device.data', ['id' => (string) $device->device_id, 'type' => 'devices'])
            ->assertJsonCount(1, 'included')
            ->assertJsonPath('included.0.type', 'devices')
            ->assertJsonPath('included.0.attributes.hostname', $device->hostname);

        $this->assertCount(2, $response->json('data'));
    }

    public function testPortPermissionShowsThePortButNotItsDevice(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        Port::factory()->for($port->device)->create();
        $user = $this->apiUser();
        $user->portsOwned()->attach($port->port_id);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ports?include=device')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', (string) $port->port_id)
            ->assertJsonPath('data.0.relationships.device.data.id', (string) $port->device_id)
            ->assertJsonMissingPath('included');

        $this->getJson("/api/v1/devices/$port->device_id")->assertForbidden();
        $this->getJson("/api/v1/ports/$port->port_id/device")->assertForbidden();
    }

    public function testDeviceRelationshipShowsThePortDevice(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        $user = $this->apiUser();
        $user->devicesOwned()->attach($port->device_id);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/ports/$port->port_id/device")
            ->assertOk()
            ->assertJsonPath('data.type', 'devices')
            ->assertJsonPath('data.id', (string) $port->device_id);
    }

    public function testDeviceRelationshipOfAnOrphanedPortIsNotFound(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        Device::whereKey($port->device_id)->delete();
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson("/api/v1/ports/$port->port_id/device")->assertNotFound();
    }

    public function testListQueriesDoNotGrowWithPorts(): void
    {
        $ports = Port::factory()->count(5)->for(Device::factory())->create()
            ->each(fn (Port $port) => $port->device()->associate(Device::factory()->create())->save());
        $ids = $ports->pluck('port_id')->implode(',');
        Sanctum::actingAs(User::factory()->admin()->create());

        $queries = function (int $pageSize) use ($ids): int {
            DeviceCache::flush();
            DB::flushQueryLog();
            $this->getJson("/api/v1/ports?filter[id][in]=$ids&page[size]=$pageSize")->assertOk();

            return count(DB::getQueryLog());
        };

        DB::enableQueryLog();
        $queries(1); // warm up user and permission caches
        $this->assertSame($queries(1), $queries(5));
    }

    public function testDeviceRelationshipRequiresAccessToThePort(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        Sanctum::actingAs($this->apiUser());

        $this->getJson("/api/v1/ports/$port->port_id/device")->assertForbidden();
    }

    public function testShowRequiresAccessToThePort(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        Sanctum::actingAs($this->apiUser());

        $this->getJson("/api/v1/ports/$port->port_id")->assertForbidden();
    }

    public function testFilterAndSort(): void
    {
        $device = Device::factory()->create();
        $eth1 = Port::factory()->for($device)->create(['ifName' => 'eth1', 'disabled' => 0]);
        $eth0 = Port::factory()->for($device)->create(['ifName' => 'eth0', 'disabled' => 0]);
        Port::factory()->for($device)->create(['ifName' => 'eth2', 'disabled' => 1]);
        Sanctum::actingAs(User::factory()->admin()->create());

        $ids = array_column($this->getJson("/api/v1/devices/$device->device_id/ports?filter[isEnabled]=true&sort=name")->assertOk()->json('data'), 'id');

        $this->assertSame([(string) $eth0->port_id, (string) $eth1->port_id], $ids);
    }

    /**
     * The document shape clients rely on. Adding to it is fine, changing or removing anything
     * needs a new API version once v1 is out of beta, see doc/Developing/API-v1.md.
     */
    public function testDocumentShapeIsFrozen(): void
    {
        $port = Port::factory()->for(Device::factory())->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $data = $this->getJson("/api/v1/ports/$port->port_id")->assertOk()->json('data');

        $this->assertSame(['id', 'type', 'attributes', 'relationships', 'links'], array_keys($data));
        $this->assertSame([
            'label',
            'ifIndex',
            'name',
            'description',
            'hardwareDescription',
            'interfaceType',
            'speed',
            'mtu',
            'duplex',
            'macAddress',
            'vlan',
            'trunk',
            'administrativeStatus',
            'operationalStatus',
            'isEnabled',
            'isIgnored',
            'isDeleted',
            'lastPolledAt',
        ], array_keys($data['attributes']));
        $this->assertSame(['device'], array_keys($data['relationships']));
    }
}
