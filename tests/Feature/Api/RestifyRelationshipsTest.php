<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\PollerCluster;
use App\Models\PollerClusterStat;
use App\Models\Application;
use App\Models\BgpPeer;
use App\Models\Bill;
use App\Models\Component;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Eventlog;
use App\Models\Syslog;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\Mempool;
use App\Models\Port;
use App\Models\PortGroup;
use App\Models\Processor;
use App\Models\Sensor;
use App\Models\Service;
use App\Models\ServiceTemplate;
use App\Models\Storage;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RestifyRelationshipsTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);

        $apiAccess = Permission::findOrCreate('api.access');
        Role::findOrCreate('admin')->givePermissionTo($apiAccess);
        Role::findOrCreate('global-read')->givePermissionTo($apiAccess);
    }

    // ── Device relationships ────────────────────────────────

    public function testDeviceShowIncludesPorts(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $ports = Port::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=ports");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', (string) $device->device_id)
            ->assertJsonCount(3, 'data.relationships.ports');
    }

    public function testDeviceShowIncludesLocation(): void
    {
        $user = User::factory()->admin()->create();
        $location = Location::factory()->withCoordinates()->create();
        $device = Device::factory()->create(['location_id' => $location->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=location");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.location.attributes.location', $location->location);
    }

    public function testDeviceShowIncludesGroups(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $groups = DeviceGroup::factory()->count(2)->create(['type' => 'static']);
        $device->groups()->attach($groups->pluck('id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=groups");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.groups');
    }

    public function testDeviceIndexIncludesRelationships(): void
    {
        $user = User::factory()->admin()->create();
        $location = Location::factory()->create();
        $device = Device::factory()->create(['location_id' => $location->id]);
        Port::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/devices?related=ports,location');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.0.relationships.ports')
            ->assertJsonPath('data.0.relationships.location.attributes.location', $location->location);
    }

    public function testDeviceWithoutRelatedParamExcludesRelationships(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Port::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}");

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.relationships');
    }

    public function testDeviceWithNullLocationReturnsNullRelationship(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create(['location_id' => null]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=location");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.location', null);
    }

    // ── Port relationships ──────────────────────────────────

    public function testPortShowIncludesDevice(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/ports/{$port->port_id}?related=device");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.device.attributes.hostname', $device->hostname);
    }

    public function testPortShowIncludesGroups(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        $portGroups = PortGroup::factory()->count(2)->create();
        $port->groups()->attach($portGroups->pluck('id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/ports/{$port->port_id}?related=groups");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.groups');
    }

    // ── Alert relationships ─────────────────────────────────

    public function testAlertShowIncludesDevice(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alerts/{$alert->id}?related=device");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.device.attributes.hostname', $device->hostname);
    }

    public function testAlertShowIncludesRule(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alerts/{$alert->id}?related=rule");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.rule.attributes.name', $rule->name);
    }

    public function testAlertIndexIncludesDeviceAndRule(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $rule = AlertRule::factory()->create();
        Alert::factory()->create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/alerts?related=device,rule');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.relationships.device.attributes.hostname', $device->hostname)
            ->assertJsonPath('data.0.relationships.rule.attributes.name', $rule->name);
    }

    // ── AlertRule relationships ──────────────────────────────

    public function testAlertRuleIncludesDevices(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create();
        $devices = Device::factory()->count(2)->create();
        $rule->devices()->attach($devices->pluck('device_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alert-rules/{$rule->id}?related=devices");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.devices');
    }

    public function testAlertRuleIncludesGroups(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create();
        $groups = DeviceGroup::factory()->count(2)->create(['type' => 'static']);
        $rule->groups()->attach($groups->pluck('id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alert-rules/{$rule->id}?related=groups");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.groups');
    }

    public function testAlertRuleIncludesLocations(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create();
        $locations = Location::factory()->count(2)->create();
        $rule->locations()->attach($locations->pluck('id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alert-rules/{$rule->id}?related=locations");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.locations');
    }

    // ── DeviceGroup relationships ───────────────────────────

    public function testDeviceGroupIncludesDevices(): void
    {
        $user = User::factory()->admin()->create();
        $group = DeviceGroup::factory()->create(['type' => 'static']);
        $devices = Device::factory()->count(3)->create();
        $group->devices()->attach($devices->pluck('device_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/device-groups/{$group->id}?related=devices");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.relationships.devices');
    }

    // ── Location relationships ──────────────────────────────

    public function testLocationIncludesDevices(): void
    {
        $user = User::factory()->admin()->create();
        $location = Location::factory()->create();
        Device::factory()->count(2)->create(['location_id' => $location->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/locations/{$location->id}?related=devices");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.devices');
    }

    // ── PortGroup relationships ─────────────────────────────

    public function testPortGroupIncludesPorts(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $portGroup = PortGroup::factory()->create();
        $ports = Port::factory()->count(3)->for($device)->create();
        $portGroup->ports()->attach($ports->pluck('port_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/port-groups/{$portGroup->id}?related=ports");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.relationships.ports');
    }

    // ── ServiceTemplate relationships ───────────────────────

    public function testServiceTemplateIncludesDevices(): void
    {
        $user = User::factory()->admin()->create();
        $template = ServiceTemplate::factory()->create();
        $devices = Device::factory()->count(2)->create();
        $template->devices()->attach($devices->pluck('device_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/service-templates/{$template->id}?related=devices");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.devices');
    }

    public function testServiceTemplateIncludesGroups(): void
    {
        $user = User::factory()->admin()->create();
        $template = ServiceTemplate::factory()->create();
        $groups = DeviceGroup::factory()->count(2)->create(['type' => 'static']);
        $template->groups()->attach($groups->pluck('id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/service-templates/{$template->id}?related=groups");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.groups');
    }

    // ── User relationships ──────────────────────────────────

    public function testUserIncludesDevicesOwned(): void
    {
        $user = User::factory()->admin()->create();
        $devices = Device::factory()->count(2)->create();
        $user->devicesOwned()->attach($devices->pluck('device_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/users/{$user->user_id}?related=devicesOwned");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.devicesOwned');
    }

    public function testUserIncludesPortsOwned(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $ports = Port::factory()->count(2)->for($device)->create();
        $user->portsOwned()->attach($ports->pluck('port_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/users/{$user->user_id}?related=portsOwned");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.portsOwned');
    }

    // ── Core monitoring relationships ─────────────────────

    public function testDeviceShowIncludesSensors(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sensor::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=sensors");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.relationships.sensors');
    }

    public function testDeviceShowIncludesProcessors(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Processor::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=processors");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.processors');
    }

    public function testDeviceShowIncludesMempools(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Mempool::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=mempools");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.mempools');
    }

    public function testDeviceShowIncludesStorage(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Storage::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=storage");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.storage');
    }

    public function testDeviceShowIncludesServices(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Service::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=services");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.services');
    }

    public function testDeviceShowIncludesComponents(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Component::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=components");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.relationships.components');
    }

    public function testDeviceShowIncludesApplications(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Application::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=applications");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.applications');
    }

    public function testDeviceShowIncludesBgpPeers(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        BgpPeer::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=bgpPeers");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.bgpPeers');
    }

    public function testDeviceShowIncludesEventlogs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Eventlog::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=eventlogs");

        $response->assertStatus(200);
        // Device creation may auto-create eventlog entries
        $this->assertGreaterThanOrEqual(3, count($response->json('data.relationships.eventlogs')));
    }

    public function testDeviceShowIncludesSyslogs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Syslog::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=syslogs");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.syslogs');
    }

    public function testDeviceShowIncludesAlertLogs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        AlertLog::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=alertLogs");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.alertLogs');
    }

    public function testAlertLogShowIncludesDevice(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $log = AlertLog::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alert-logs/{$log->id}?related=device");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.device.attributes.hostname', $device->hostname);
    }

    public function testAlertLogShowIncludesRule(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $rule = AlertRule::factory()->create();
        $log = AlertLog::factory()->for($device)->create(['rule_id' => $rule->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alert-logs/{$log->id}?related=rule");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.rule.attributes.name', $rule->name);
    }

    public function testBgpPeerShowIncludesDevice(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $peer = BgpPeer::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/bgp-peers/{$peer->bgpPeer_id}?related=device");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.device.attributes.hostname', $device->hostname);
    }

    public function testDeviceShowIncludesInventory(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        EntPhysical::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=inventory");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.relationships.inventory');
    }

    public function testEntPhysicalShowIncludesDevice(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $entity = EntPhysical::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/inventory/{$entity->entPhysical_id}?related=device");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.device.attributes.hostname', $device->hostname);
    }

    public function testPollerClusterShowIncludesStats(): void
    {
        $user = User::factory()->admin()->create();
        $cluster = PollerCluster::factory()->create();
        PollerClusterStat::factory()->count(3)->create(['parent_poller' => $cluster->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/poller-clusters/{$cluster->id}?related=stats");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.relationships.stats');
    }

    public function testBillShowIncludesPorts(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $bill = Bill::factory()->create();
        $ports = Port::factory()->count(2)->for($device)->create();
        $bill->ports()->attach($ports->pluck('port_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/bills/{$bill->bill_id}?related=ports");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.ports');
    }

    public function testComponentShowIncludesDevice(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $component = Component::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/components/{$component->id}?related=device");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.device.attributes.hostname', $device->hostname);
    }

    public function testSensorShowIncludesDevice(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $sensor = Sensor::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/sensors/{$sensor->sensor_id}?related=device");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.device.attributes.hostname', $device->hostname);
    }

    // ── Multiple relationships in one request ───────────────

    public function testMultipleRelationshipsCanBeRequested(): void
    {
        $user = User::factory()->admin()->create();
        $location = Location::factory()->create();
        $device = Device::factory()->create(['location_id' => $location->id]);
        $group = DeviceGroup::factory()->create(['type' => 'static']);
        $device->groups()->attach($group->id);
        Port::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=ports,location,groups");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.ports')
            ->assertJsonPath('data.relationships.location.attributes.location', $location->location)
            ->assertJsonCount(1, 'data.relationships.groups');
    }

    // ── Empty relationships ─────────────────────────────────

    public function testEmptyHasManyReturnsEmptyArray(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=ports");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.ports', []);
    }

    public function testEmptyBelongsToManyReturnsEmptyArray(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=groups");

        $response->assertStatus(200)
            ->assertJsonPath('data.relationships.groups', []);
    }

    // ── Relationship permission tests ───────────────────────

    private function createCustomRoleUser(array $permissions): User
    {
        $role = Role::findOrCreate('custom-test-role');
        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }
        $role->syncPermissions($permissions);
        $user = User::factory()->create();
        $user->assignRole('custom-test-role');

        return $user;
    }

    public function testUserWithoutApiAccessCannotReachRelationships(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Port::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/devices/{$device->device_id}?related=ports")
            ->assertStatus(403);
    }

    public function testGlobalReadCanSeeRelatedResources(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        Port::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/devices/{$device->device_id}?related=ports");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.relationships.ports');
    }

    public function testCustomRoleCannotSeeUnauthorizedBelongsToManyRelated(): void
    {
        // User can access alert-rules but NOT devices
        $user = $this->createCustomRoleUser([
            'api.access',
            'alert-rule.viewAny',
            'alert-rule.view',
        ]);
        $rule = AlertRule::factory()->create();
        $devices = Device::factory()->count(2)->create();
        $rule->devices()->attach($devices->pluck('device_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alert-rules/{$rule->id}?related=devices");

        $response->assertStatus(200);

        // Devices should be filtered out (null) since user lacks device.view permission
        $relationships = $response->json('data.relationships.devices');
        $nonNullDevices = collect($relationships)->filter()->values();
        $this->assertEmpty($nonNullDevices, 'User without device.view should not see related devices');
    }

    public function testCustomRoleCannotSeeUnauthorizedBelongsToRelated(): void
    {
        // User can access alerts but NOT devices
        $user = $this->createCustomRoleUser([
            'api.access',
            'alert.viewAny',
            'alert.view',
        ]);
        $device = Device::factory()->create();
        $alert = Alert::factory()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        // BelongsTo aborts with 403 when the related resource is unauthorized
        $response = $this->getJson("/api/v1/alerts/{$alert->id}?related=device");

        // Either 403 (BelongsTo abort) or 200 with null relationship
        $this->assertTrue(
            $response->status() === 403
            || $response->json('data.relationships.device') === null,
            'Unauthorized BelongsTo should return 403 or null relationship'
        );
    }

    public function testAdminCanSeeAllRelatedResources(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create();
        $devices = Device::factory()->count(3)->create();
        $rule->devices()->attach($devices->pluck('device_id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alert-rules/{$rule->id}?related=devices");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.relationships.devices');

        // Verify none are null
        $relationships = $response->json('data.relationships.devices');
        $nullCount = collect($relationships)->filter(fn ($item) => $item === null)->count();
        $this->assertEquals(0, $nullCount, 'Admin should see all related devices without null entries');
    }

    public function testDeviceGroupRelatedDevicesFilteredByPermission(): void
    {
        // User can access device-groups and has device view, but only for specific devices
        $user = $this->createCustomRoleUser([
            'api.access',
            'device-group.viewAny',
            'device-group.view',
            // No device.view — user relies on per-device permission via devices_perms
        ]);

        $group = DeviceGroup::factory()->create(['type' => 'static']);
        $deviceAccessible = Device::factory()->create();
        $deviceInaccessible = Device::factory()->create();
        $group->devices()->attach([
            $deviceAccessible->device_id,
            $deviceInaccessible->device_id,
        ]);

        // Grant access to only one device
        $user->devicesOwned()->attach($deviceAccessible->device_id);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/device-groups/{$group->id}?related=devices");

        $response->assertStatus(200);

        // Filter out nulls — only the permitted device should remain
        $relationships = $response->json('data.relationships.devices');
        $visibleDevices = collect($relationships)->filter()->values();
        $this->assertCount(1, $visibleDevices, 'User should only see the device they have access to');
        $this->assertEquals(
            (string) $deviceAccessible->device_id,
            $visibleDevices->first()['id'],
            'The visible device should be the one the user has permission for'
        );
    }

    public function testCustomRoleCannotSeeUnauthorizedDeviceGroupsViaAlertRule(): void
    {
        // User can access alert-rules but NOT device-groups
        $user = $this->createCustomRoleUser([
            'api.access',
            'alert-rule.viewAny',
            'alert-rule.view',
        ]);
        $rule = AlertRule::factory()->create();
        $groups = DeviceGroup::factory()->count(2)->create(['type' => 'static']);
        $rule->groups()->attach($groups->pluck('id'));
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/alert-rules/{$rule->id}?related=groups");

        $response->assertStatus(200);

        // Device groups should be filtered out since user lacks device-group.view
        $relationships = $response->json('data.relationships.groups');
        $nonNullGroups = collect($relationships)->filter()->values();
        $this->assertEmpty($nonNullGroups, 'User without device-group.view should not see related groups');
    }
}
