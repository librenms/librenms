<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\IsisAdjacency;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class IsisAdjacencyApiTest extends DBTestCase
{
    use DatabaseTransactions;

    private const string URI = '/api/v1/routing/isis-adjacencies';

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('api.v1.enabled', true);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions, string $role = 'api-test'): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role));
        foreach (['api.access', ...$permissions] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission));
        }

        return $user;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $portAttributes
     */
    private function adjacencyOnDevice(Device $device, array $attributes = [], array $portAttributes = []): IsisAdjacency
    {
        $port = Port::factory()->create(['device_id' => $device->device_id, ...$portAttributes]);

        return IsisAdjacency::factory()->create([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'ifIndex' => $port->ifIndex,
            ...$attributes,
        ]);
    }

    public function testShowReturnsApiAttributes(): void
    {
        $device = Device::factory()->create();
        $adjacency = $this->adjacencyOnDevice($device, [
            'index' => '1',
            'isisCircAdminState' => 'on',
            'isisISAdjState' => 'up',
            'isisISAdjNeighSysType' => 'l1l2IntermediateSystem',
            'isisISAdjNeighSysID' => '0000.0000.0001',
            'isisISAdjNeighPriority' => '64',
            'isisISAdjLastUpTime' => 1700000000,
            'isisISAdjAreaAddress' => '49.0001',
            'isisISAdjIPAddrType' => 'ipv4',
            'isisISAdjIPAddrAddress' => '10.0.0.1',
        ], [
            'ifName' => 'Gi0/1',
            'ifDescr' => 'GigabitEthernet0/1',
            'ifAlias' => 'uplink to core',
        ]);
        Sanctum::actingAs($this->userWithPermissions([], 'global-read'));

        $this->getJson(self::URI . '/' . $adjacency->id)
            ->assertOk()
            ->assertJsonPath('data.type', 'isis-adjacencies')
            ->assertJsonPath('data.id', (string) $adjacency->id)
            ->assertJsonPath('data.attributes', [
                'deviceId' => $device->device_id,
                'portId' => $adjacency->port_id,
                'ifIndex' => $adjacency->ifIndex,
                'port' => [
                    'id' => $adjacency->port_id,
                    'ifName' => 'Gi0/1',
                    'ifDescr' => 'GigabitEthernet0/1',
                    'ifAlias' => 'uplink to core',
                ],
                'index' => '1',
                'isisCircAdminState' => 'on',
                'isisISAdjState' => 'up',
                'isisISAdjNeighSysType' => 'l1l2IntermediateSystem',
                'isisISAdjNeighSysID' => '0000.0000.0001',
                'isisISAdjNeighPriority' => '64',
                'isisISAdjLastUpTime' => 1700000000,
                'isisISAdjAreaAddress' => '49.0001',
                'isisISAdjIPAddrType' => 'ipv4',
                'isisISAdjIPAddrAddress' => '10.0.0.1',
            ]);
    }

    public function testIndexIncludesPortInformation(): void
    {
        $device = Device::factory()->create();
        $adjacency = $this->adjacencyOnDevice($device, [], [
            'ifName' => 'Gi0/2',
            'ifDescr' => 'GigabitEthernet0/2',
            'ifAlias' => 'downlink',
        ]);
        Sanctum::actingAs($this->userWithPermissions(['routing.viewAll']));

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonPath('data.0.attributes.port.id', $adjacency->port_id)
            ->assertJsonPath('data.0.attributes.port.ifName', 'Gi0/2')
            ->assertJsonPath('data.0.attributes.port.ifDescr', 'GigabitEthernet0/2')
            ->assertJsonPath('data.0.attributes.port.ifAlias', 'downlink');
    }

    public function testIndexFiltersByDeviceId(): void
    {
        $device = Device::factory()->create();
        $other = Device::factory()->create();
        $this->adjacencyOnDevice($device, ['isisISAdjNeighSysID' => '0000.0000.000a']);
        $this->adjacencyOnDevice($other, ['isisISAdjNeighSysID' => '0000.0000.000b']);
        Sanctum::actingAs($this->userWithPermissions([], 'global-read'));

        $this->getJson(self::URI . '?device_id=' . $device->device_id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.attributes.deviceId', $device->device_id)
            ->assertJsonPath('data.0.attributes.isisISAdjNeighSysID', '0000.0000.000a');
    }

    public function testIndexFiltersByPortId(): void
    {
        $device = Device::factory()->create();
        $wanted = $this->adjacencyOnDevice($device);
        $this->adjacencyOnDevice($device);
        Sanctum::actingAs($this->userWithPermissions([], 'global-read'));

        $this->getJson(self::URI . '?port_id=' . $wanted->port_id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.attributes.portId', $wanted->port_id);
    }

    public function testApiAccessPermissionIsRequired(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('global-read'));
        Sanctum::actingAs($user);

        $this->getJson(self::URI)->assertForbidden();
    }

    public function testViewAllPermissionListsEveryAdjacency(): void
    {
        $this->adjacencyOnDevice(Device::factory()->create(), ['isisISAdjNeighSysID' => 'one']);
        $this->adjacencyOnDevice(Device::factory()->create(), ['isisISAdjNeighSysID' => 'two']);
        Sanctum::actingAs($this->userWithPermissions(['routing.viewAll']));

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');
    }

    public function testViewPermissionOnlyListsAdjacenciesOnAccessiblePorts(): void
    {
        $accessible = Device::factory()->create();
        $other = Device::factory()->create();
        $visible = $this->adjacencyOnDevice($accessible, ['isisISAdjNeighSysID' => 'visible']);
        $hidden = $this->adjacencyOnDevice($other, ['isisISAdjNeighSysID' => 'hidden']);

        // IS-IS adjacencies belong to ports, so the routing view policy authorizes each one with
        // canAccessPort(): the user needs access to the port.
        $user = $this->userWithPermissions(['routing.view']);
        $user->portsOwned()->attach($visible->port_id);
        Sanctum::actingAs($user);

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.isisISAdjNeighSysID', 'visible');
        $this->getJson(self::URI . '/' . $visible->id)->assertOk();
        $this->getJson(self::URI . '/' . $hidden->id)->assertNotFound();
    }

    public function testViewPermissionWithDeviceAccessSeesDeviceAdjacencies(): void
    {
        // Device access grants visibility to that device's adjacencies, the same as the web
        // IS-IS page, and meta.total matches the rows returned.
        $accessible = Device::factory()->create();
        $other = Device::factory()->create();
        $visible = $this->adjacencyOnDevice($accessible, ['isisISAdjNeighSysID' => 'visible']);
        $hidden = $this->adjacencyOnDevice($other, ['isisISAdjNeighSysID' => 'hidden']);

        $user = $this->userWithPermissions(['routing.view']);
        $user->devicesOwned()->attach($accessible->device_id);
        Sanctum::actingAs($user);

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.isisISAdjNeighSysID', 'visible');
        $this->getJson(self::URI . '/' . $visible->id)->assertOk();
        $this->getJson(self::URI . '/' . $hidden->id)->assertNotFound();
    }

    public function testOnlyServedUnderRoutingPrefix(): void
    {
        // The prefix confines the repository, so the un-grouped path is not authorized.
        $device = Device::factory()->create();
        $this->adjacencyOnDevice($device);
        Sanctum::actingAs($this->userWithPermissions([], 'global-read'));

        $this->getJson('/api/v1/isis-adjacencies')->assertForbidden();
    }

    public function testWritesAreDeniedEvenForAdmin(): void
    {
        $device = Device::factory()->create();
        $adjacency = $this->adjacencyOnDevice($device, ['isisISAdjState' => 'up']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson(self::URI, ['isisISAdjState' => 'down'])->assertForbidden();
        $this->putJson(self::URI . '/' . $adjacency->id, ['isisISAdjState' => 'down'])->assertForbidden();
        $this->patchJson(self::URI . '/' . $adjacency->id, ['isisISAdjState' => 'down'])->assertForbidden();
        $this->deleteJson(self::URI . '/' . $adjacency->id)->assertForbidden();

        $this->assertDatabaseHas('isis_adjacencies', ['id' => $adjacency->id, 'isisISAdjState' => 'up']);
    }
}
