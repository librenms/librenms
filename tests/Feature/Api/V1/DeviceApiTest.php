<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;

final class DeviceApiTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        LibrenmsConfig::set('api.v1.enabled', true);
    }

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    /**
     * A user with the user role who can only see the given devices.
     */
    private function actingAsUserOf(Device ...$devices): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo(Permission::findOrCreate('api.access'));
        $user->devicesOwned()->attach(array_map(fn (Device $device) => $device->device_id, $devices));

        Sanctum::actingAs($user);
    }

    public function testShowIsAJsonApiDocument(): void
    {
        $device = Device::factory()->create(['sysName' => 'core1', 'disabled' => 1, 'status' => 1]);
        $this->actingAsAdmin();

        $this->getJson("/api/v1/devices/$device->device_id")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json')
            ->assertJsonPath('data.id', (string) $device->device_id)
            ->assertJsonPath('data.type', 'devices')
            ->assertJsonPath('data.attributes.hostname', $device->hostname)
            ->assertJsonPath('data.attributes.systemName', 'core1')
            ->assertJsonPath('data.attributes.isUp', true)
            ->assertJsonPath('data.attributes.isEnabled', false)
            ->assertJsonMissingPath('data.attributes.sysName')
            ->assertJsonPath('data.links.self', route('api.v1.devices.show', $device->device_id))
            ->assertJsonPath('data.relationships.ports.links.related', route('api.v1.devices.ports', $device->device_id))
            ->assertJsonMissingPath('data.relationships.ports.data');
    }

    public function testIndexIsPaginated(): void
    {
        Device::factory()->count(3)->create();
        $this->actingAsAdmin();
        $total = Device::count();

        $this->getJson('/api/v1/devices?page[size]=2&page[number]=2')
            ->assertOk()
            ->assertJsonCount(min(2, $total - 2), 'data')
            ->assertJsonPath('meta.page.currentPage', 2)
            ->assertJsonPath('meta.page.perPage', 2)
            ->assertJsonPath('meta.page.total', $total)
            ->assertJsonPath('links.prev', url('/api/v1/devices?' . http_build_query(['page' => ['number' => 1, 'size' => 2]])));
    }

    public function testIndexOnlyListsAccessibleDevices(): void
    {
        $visible = Device::factory()->create();
        Device::factory()->create();
        $this->actingAsUserOf($visible);

        $this->getJson('/api/v1/devices')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', (string) $visible->device_id);
    }

    public function testShowRequiresAccessToTheDevice(): void
    {
        $visible = Device::factory()->create();
        $hidden = Device::factory()->create();
        $this->actingAsUserOf($visible);

        $this->getJson("/api/v1/devices/$visible->device_id")->assertOk();
        $this->getJson("/api/v1/devices/$hidden->device_id")->assertForbidden();
    }

    public function testFilterAndSort(): void
    {
        $b = Device::factory()->create(['os' => 'apifilteros', 'hostname' => 'b.example.com', 'disabled' => 0]);
        $a = Device::factory()->create(['os' => 'apifilteros', 'hostname' => 'a.example.com', 'disabled' => 0]);
        Device::factory()->create(['os' => 'apifilteros', 'hostname' => 'c.example.com', 'disabled' => 1]);
        Device::factory()->create(['os' => 'otheros']);
        $this->actingAsAdmin();

        $ids = fn (string $query) => array_column($this->getJson("/api/v1/devices?$query")->assertOk()->json('data'), 'id');

        $this->assertSame([(string) $a->device_id, (string) $b->device_id], $ids('filter[os]=apifilteros&filter[isEnabled]=true&sort=hostname'));
        $this->assertSame([(string) $b->device_id, (string) $a->device_id], $ids('filter[os][eq]=apifilteros&filter[isEnabled]=true&sort=-hostname'));
        $this->assertCount(1, $ids('filter[os]=apifilteros&filter[hostname][starts_with]=c.'));
        $this->assertCount(3, $ids('filter[os]=apifilteros&filter[isEnabled][in]=true,false'));
    }

    public function testSparseFieldsets(): void
    {
        $device = Device::factory()->create();
        $this->actingAsAdmin();

        $attributes = $this->getJson("/api/v1/devices/$device->device_id?fields[devices]=hostname,os")->assertOk()->json('data.attributes');

        $this->assertSame(['hostname', 'os'], array_keys($attributes));
    }

    public function testPortsRelationshipListsTheDevicePorts(): void
    {
        $device = Device::factory()->create();
        $ports = Port::factory()->count(2)->for($device)->create();
        Port::factory()->for(Device::factory())->create();
        $this->actingAsUserOf($device);

        $response = $this->getJson("/api/v1/devices/$device->device_id/ports")
            ->assertOk()
            ->assertJsonPath('data.0.type', 'ports')
            ->assertJsonPath('meta.page.total', 2);

        $this->assertEqualsCanonicalizing($ports->pluck('port_id')->map(fn ($id) => (string) $id)->all(), array_column($response->json('data'), 'id'));
    }

    public function testPortsRelationshipRequiresAccessToTheDevice(): void
    {
        $visible = Device::factory()->create();
        $hidden = Device::factory()->create();
        $this->actingAsUserOf($visible);

        $this->getJson("/api/v1/devices/$hidden->device_id/ports")->assertForbidden();
    }

    /**
     * The document shape clients rely on. Adding to it is fine, changing or removing anything
     * needs a new API version once v1 is out of beta, see doc/Developing/API-v1.md.
     */
    public function testDocumentShapeIsFrozen(): void
    {
        $device = Device::factory()->create();
        Sanctum::actingAs(User::factory()->admin()->create());

        $data = $this->getJson("/api/v1/devices/$device->device_id")->assertOk()->json('data');

        $this->assertSame(['id', 'type', 'attributes', 'relationships', 'links'], array_keys($data));
        $this->assertSame([
            'hostname',
            'displayName',
            'ipAddress',
            'description',
            'systemName',
            'systemDescription',
            'systemContact',
            'sysObjectID',
            'os',
            'osVersion',
            'osFeatures',
            'hardware',
            'serialNumber',
            'deviceType',
            'notes',
            'bgpLocalAs',
            'isUp',
            'statusReason',
            'isEnabled',
            'isIgnored',
            'isStatusIgnored',
            'isAlertingEnabled',
            'uptime',
            'lastPollDuration',
            'lastDiscoveryDuration',
            'lastPolledAt',
            'lastDiscoveredAt',
            'createdAt',
        ], array_keys($data['attributes']));
        $this->assertSame(['ports'], array_keys($data['relationships']));
    }
}
