<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\Port;
use App\Models\PortGroup;
use App\Models\ServiceTemplate;
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
}
