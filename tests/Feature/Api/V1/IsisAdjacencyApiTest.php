<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\IsisAdjacency;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
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
        $port = Port::factory()->for($device)->create($portAttributes);

        return IsisAdjacency::factory()->create([
            'device_id' => $device->device_id,
            'port_id' => $port->port_id,
            'ifIndex' => $port->ifIndex,
            ...$attributes,
        ]);
    }

    public function testShowIsAJsonApiDocument(): void
    {
        $device = Device::factory()->create();
        $adjacency = $this->adjacencyOnDevice($device, [
            'isisISAdjState' => 'up',
            'isisISAdjNeighSysID' => '0000.0000.0001',
            'isisISAdjIPAddrAddress' => '10.0.0.1',
        ]);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . '/' . $adjacency->id)
            ->assertOk()
            ->assertJsonPath('data.id', (string) $adjacency->id)
            ->assertJsonPath('data.type', 'isis-adjacencies')
            ->assertJsonPath('data.attributes.state', 'up')
            ->assertJsonPath('data.attributes.neighborSystemId', '0000.0000.0001')
            ->assertJsonPath('data.attributes.neighborIpAddress', '10.0.0.1')
            ->assertJsonMissingPath('data.attributes.deviceId')
            ->assertJsonMissingPath('data.attributes.portId')
            ->assertJsonMissingPath('data.attributes.isisISAdjState')
            ->assertJsonPath('data.relationships.device.links.related', route('api.v1.routing.isis-adjacencies.device', $adjacency->id))
            ->assertJsonPath('data.relationships.port.links.related', route('api.v1.routing.isis-adjacencies.port', $adjacency->id))
            ->assertJsonMissingPath('data.relationships.device.data')
            ->assertJsonMissingPath('included');
    }

    public function testLevelMetricsAreExposed(): void
    {
        $adjacency = $this->adjacencyOnDevice(Device::factory()->create(), [
            'isisCircLevelMetricL1' => 10,
            'isisCircLevelWideMetricL1' => 16777215,
            'isisCircLevelMetricL2' => null,
            'isisCircLevelWideMetricL2' => null,
        ]);
        Sanctum::actingAs(User::factory()->admin()->create());

        $response = $this->getJson(self::URI . '/' . $adjacency->id)->assertOk();

        // a configured level reports its metric, an unused level is null
        $this->assertEquals(10, $response->json('data.attributes.level1Metric'));
        $this->assertEquals(16777215, $response->json('data.attributes.level1WideMetric'));
        $this->assertNull($response->json('data.attributes.level2Metric'));
        $this->assertNull($response->json('data.attributes.level2WideMetric'));
    }

    public function testIncludeDeviceAndPort(): void
    {
        $device = Device::factory()->create();
        $adjacency = $this->adjacencyOnDevice($device, [], ['ifName' => 'Gi0/1']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . '/' . $adjacency->id . '?include=device,port')
            ->assertOk()
            ->assertJsonPath('data.relationships.device.data', ['id' => (string) $device->device_id, 'type' => 'devices'])
            ->assertJsonPath('data.relationships.port.data', ['id' => (string) $adjacency->port_id, 'type' => 'ports'])
            ->assertJsonCount(2, 'included')
            ->assertJsonFragment(['type' => 'devices', 'id' => (string) $device->device_id])
            ->assertJsonFragment(['type' => 'ports', 'id' => (string) $adjacency->port_id]);
    }

    public function testFilterByDevice(): void
    {
        $device = Device::factory()->create();
        $other = Device::factory()->create();
        $this->adjacencyOnDevice($device, ['isisISAdjNeighSysID' => '0000.0000.000a']);
        $this->adjacencyOnDevice($other, ['isisISAdjNeighSysID' => '0000.0000.000b']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . "?filter[device]=$device->device_id")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.neighborSystemId', '0000.0000.000a');
    }

    public function testFilterByPort(): void
    {
        $device = Device::factory()->create();
        $wanted = $this->adjacencyOnDevice($device);
        $this->adjacencyOnDevice($device);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . "?filter[port]=$wanted->port_id")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', (string) $wanted->id);
    }

    public function testSortByNeighborSystemId(): void
    {
        $device = Device::factory()->create();
        $b = $this->adjacencyOnDevice($device, ['isisISAdjNeighSysID' => 'bbbb']);
        $a = $this->adjacencyOnDevice($device, ['isisISAdjNeighSysID' => 'aaaa']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $ids = array_column($this->getJson(self::URI . '?sort=neighborSystemId')->assertOk()->json('data'), 'id');

        $this->assertSame([(string) $a->id, (string) $b->id], $ids);
    }

    public function testApiAccessPermissionIsRequired(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('global-read'));
        Sanctum::actingAs($user);

        $this->getJson(self::URI)->assertForbidden();
    }

    public function testViewAnyPermissionIsRequired(): void
    {
        Sanctum::actingAs($this->userWithPermissions([]));

        $this->getJson(self::URI)->assertForbidden();
    }

    public function testViewAllListsEveryAdjacency(): void
    {
        $this->adjacencyOnDevice(Device::factory()->create(), ['isisISAdjNeighSysID' => 'one']);
        $this->adjacencyOnDevice(Device::factory()->create(), ['isisISAdjNeighSysID' => 'two']);
        Sanctum::actingAs($this->userWithPermissions(['routing.viewAll']));

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.page.total', 2);
    }

    public function testViewPermissionOnlyListsAdjacenciesOnAccessiblePorts(): void
    {
        $visible = $this->adjacencyOnDevice(Device::factory()->create(), ['isisISAdjNeighSysID' => 'visible']);
        $hidden = $this->adjacencyOnDevice(Device::factory()->create(), ['isisISAdjNeighSysID' => 'hidden']);

        // IS-IS adjacencies belong to ports, a port grant gives access to its adjacency.
        $user = $this->userWithPermissions(['routing.view']);
        $user->portsOwned()->attach($visible->port_id);
        Sanctum::actingAs($user);

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.neighborSystemId', 'visible');
        $this->getJson(self::URI . '/' . $visible->id)->assertOk();
        $this->getJson(self::URI . '/' . $hidden->id)->assertForbidden();
    }

    public function testViewPermissionWithDeviceAccessSeesDeviceAdjacencies(): void
    {
        // Device access grants visibility to that device's adjacencies, matching the web IS-IS page
        // and the Port policy, so list and show agree.
        $accessible = Device::factory()->create();
        $visible = $this->adjacencyOnDevice($accessible, ['isisISAdjNeighSysID' => 'visible']);
        $hidden = $this->adjacencyOnDevice(Device::factory()->create(), ['isisISAdjNeighSysID' => 'hidden']);

        $user = $this->userWithPermissions(['routing.view']);
        $user->devicesOwned()->attach($accessible->device_id);
        Sanctum::actingAs($user);

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.attributes.neighborSystemId', 'visible');
        $this->getJson(self::URI . '/' . $visible->id)->assertOk();
        $this->getJson(self::URI . '/' . $hidden->id)->assertForbidden();
    }

    public function testDeviceAndPortRelationshipEndpoints(): void
    {
        $device = Device::factory()->create();
        $adjacency = $this->adjacencyOnDevice($device, [], ['ifName' => 'Gi0/1']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . '/' . $adjacency->id . '/device')
            ->assertOk()
            ->assertJsonPath('data.type', 'devices')
            ->assertJsonPath('data.id', (string) $device->device_id);

        $this->getJson(self::URI . '/' . $adjacency->id . '/port')
            ->assertOk()
            ->assertJsonPath('data.type', 'ports')
            ->assertJsonPath('data.id', (string) $adjacency->port_id)
            ->assertJsonPath('data.attributes.name', 'Gi0/1');
    }

    public function testPortRelationshipOfAnAdjacencyWithoutAPortIsNotFound(): void
    {
        $device = Device::factory()->create();
        $adjacency = IsisAdjacency::factory()->create(['device_id' => $device->device_id, 'port_id' => null]);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->getJson(self::URI . '/' . $adjacency->id . '/port')->assertNotFound();
    }

    public function testRelationshipEndpointsRequireAccessToTheAdjacency(): void
    {
        $adjacency = $this->adjacencyOnDevice(Device::factory()->create());
        Sanctum::actingAs($this->userWithPermissions(['routing.view']));

        $this->getJson(self::URI . '/' . $adjacency->id . '/device')->assertForbidden();
        $this->getJson(self::URI . '/' . $adjacency->id . '/port')->assertForbidden();
    }

    public function testWritesAreNotAllowed(): void
    {
        $device = Device::factory()->create();
        $adjacency = $this->adjacencyOnDevice($device, ['isisISAdjState' => 'up']);
        Sanctum::actingAs(User::factory()->admin()->create());

        $this->postJson(self::URI, [])->assertStatus(405);
        $this->putJson(self::URI . '/' . $adjacency->id, [])->assertStatus(405);
        $this->patchJson(self::URI . '/' . $adjacency->id, [])->assertStatus(405);
        $this->deleteJson(self::URI . '/' . $adjacency->id)->assertStatus(405);

        $this->assertDatabaseHas('isis_adjacencies', ['id' => $adjacency->id, 'isisISAdjState' => 'up']);
    }

    public function testListQueriesDoNotGrowWithAdjacencies(): void
    {
        $ids = collect(range(1, 5))
            ->map(fn () => $this->adjacencyOnDevice(Device::factory()->create())->id)
            ->implode(',');
        Sanctum::actingAs(User::factory()->admin()->create());

        $queries = function (int $pageSize) use ($ids): int {
            DeviceCache::flush();
            DB::flushQueryLog();
            $this->getJson(self::URI . "?filter[id][in]=$ids&include=device,port&page[size]=$pageSize")->assertOk();

            return count(DB::getQueryLog());
        };

        DB::enableQueryLog();
        $queries(1); // warm up user and permission caches
        $this->assertSame($queries(1), $queries(5));
    }

    /**
     * The document shape clients rely on. Adding to it is fine, changing or removing anything
     * needs a new API version once v1 is out of beta, see doc/Developing/API-v1.md.
     */
    public function testDocumentShapeIsFrozen(): void
    {
        $adjacency = $this->adjacencyOnDevice(Device::factory()->create());
        Sanctum::actingAs(User::factory()->admin()->create());

        $data = $this->getJson(self::URI . '/' . $adjacency->id)->assertOk()->json('data');

        $this->assertSame(['id', 'type', 'attributes', 'relationships', 'links'], array_keys($data));
        $this->assertSame([
            'index',
            'ifIndex',
            'circuitAdministrativeStatus',
            'level1Metric',
            'level1WideMetric',
            'level2Metric',
            'level2WideMetric',
            'state',
            'neighborSystemType',
            'neighborSystemId',
            'neighborPriority',
            'lastUpTime',
            'neighborAreaAddress',
            'neighborIpAddressType',
            'neighborIpAddress',
        ], array_keys($data['attributes']));
        $this->assertSame(['device', 'port'], array_keys($data['relationships']));
    }
}
