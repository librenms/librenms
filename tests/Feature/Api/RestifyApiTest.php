<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Http\Middleware\EnforceJsonApi;
use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\AlertSchedule;
use App\Models\AlertTemplate;
use App\Models\AlertTransport;
use App\Models\Application;
use App\Models\AuthLog;
use App\Models\BgpPeer;
use App\Models\Bill;
use App\Models\Component;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Eventlog;
use App\Models\IsisAdjacency;
use App\Models\MplsLsp;
use App\Models\MplsLspPath;
use App\Models\MplsSap;
use App\Models\MplsSdp;
use App\Models\MplsSdpBind;
use App\Models\MplsService;
use App\Models\MplsTunnelArHop;
use App\Models\Ipv4Address;
use App\Models\AccessPoint;
use App\Models\Availability;
use App\Models\CefSwitching;
use App\Models\DeviceOutage;
use App\Models\DiskIo;
use App\Models\Ipv4Mac;
use App\Models\IpsecTunnel;
use App\Models\Ipv6Nd;
use App\Models\PortVlan;
use App\Models\Ipv4Network;
use App\Models\Ipv6Address;
use App\Models\Ipv6Network;
use App\Models\Link;
use App\Models\PortAdsl;
use App\Models\PortSecurity;
use App\Models\PortsFdb;
use App\Models\PortsNac;
use App\Models\PortStack;
use App\Models\PortStatistic;
use App\Models\PortVdsl;
use App\Models\PortStp;
use App\Models\MplsTunnelCHop;
use App\Models\Pseudowire;
use App\Models\Sla;
use App\Models\Stp;
use App\Models\Transceiver;
use App\Models\Vlan;
use App\Models\WirelessSensor;
use App\Models\OspfArea;
use App\Models\OspfInstance;
use App\Models\OspfNbr;
use App\Models\OspfPort;
use App\Models\Ospfv3Area;
use App\Models\Ospfv3Instance;
use App\Models\Ospfv3Nbr;
use App\Models\Ospfv3Port;
use App\Models\Route;
use App\Models\Syslog;
use App\Models\Vrf;
use App\Models\VrfLite;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\Mempool;
use App\Models\PollerCluster;
use App\Models\PollerClusterStat;
use App\Models\PollerGroup;
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
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RestifyApiTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);

        // Ensure api.access permission exists and is assigned to admin and global-read roles
        $apiAccess = Permission::findOrCreate('api.access');
        Role::findOrCreate('admin')->givePermissionTo($apiAccess);
        Role::findOrCreate('global-read')->givePermissionTo($apiAccess);
    }

    /**
     * Send a JSON:API POST request with the correct Content-Type.
     */
    protected function postJsonApi(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('POST', $uri, $data, array_merge([
            'Content-Type' => EnforceJsonApi::CONTENT_TYPE,
            'Accept' => EnforceJsonApi::CONTENT_TYPE,
        ], $headers));
    }

    /**
     * Send a JSON:API PUT request with the correct Content-Type.
     */
    protected function putJsonApi(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PUT', $uri, $data, array_merge([
            'Content-Type' => EnforceJsonApi::CONTENT_TYPE,
            'Accept' => EnforceJsonApi::CONTENT_TYPE,
        ], $headers));
    }

    /**
     * Send a JSON:API DELETE request.
     */
    protected function deleteJsonApi(string $uri, array $headers = []): TestResponse
    {
        return $this->json('DELETE', $uri, [], array_merge([
            'Accept' => EnforceJsonApi::CONTENT_TYPE,
        ], $headers));
    }

    // ── JSON:API Content-Type ───────────────────────────────

    public function testResponseHasJsonApiContentType(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/devices')
            ->assertStatus(200)
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE);
    }

    public function testWrongContentTypeReturns415(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->json('POST', '/api/v1/alert-templates', [
            'name' => 'test',
            'template' => 'body',
        ], ['Content-Type' => 'text/plain'])
            ->assertStatus(415);
    }

    public function testApplicationJsonContentTypeIsAccepted(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/alert-templates', [
            'name' => 'json-compat-test',
            'template' => 'body',
        ])->assertStatus(201);
    }

    // ── Authentication ───────────────────────────────────────

    public function testUnauthenticatedRequestReturns401(): void
    {
        $this->getJson('/api/v1/devices')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testInvalidTokenReturns401(): void
    {
        $this->getJson('/api/v1/devices', [
            'Authorization' => 'Bearer invalid-token',
        ])->assertStatus(401);
    }

    public function testValidTokenAuthenticates(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/devices')
            ->assertStatus(200);
    }

    // ── Devices ──────────────────────────────────────────────

    public function testAdminCanListDevices(): void
    {
        $user = User::factory()->admin()->create();
        $devices = Device::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/devices');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 3)
            ->assertJsonCount(3, 'data');
    }

    public function testGlobalReadCanListDevices(): void
    {
        $user = User::factory()->read()->create();
        Device::factory()->count(2)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/devices')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testRegularUserCannotAccessDevices(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Device::factory()->count(2)->create();
        Sanctum::actingAs($user);

        // Users without global read are blocked by allowRestify policy
        $this->getJson('/api/v1/devices')
            ->assertStatus(403);
    }

    public function testAdminCanShowDevice(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/devices/{$device->device_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.hostname', $device->hostname);
    }

    public function testDeviceFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create([
            'hostname' => 'router01.example.com',
            'os' => 'iosxe',
            'status' => 1,
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/devices/{$device->device_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.hostname', 'router01.example.com')
            ->assertJsonPath('data.attributes.os', 'iosxe')
            ->assertJsonPath('data.attributes.isUp', true);
    }

    public function testDeviceSearchByHostname(): void
    {
        $user = User::factory()->admin()->create();
        Device::factory()->create(['hostname' => 'router01.example.com']);
        Device::factory()->create(['hostname' => 'switch01.example.com']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/devices?search=router')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.attributes.hostname', 'router01.example.com');
    }

    public function testDevicePagination(): void
    {
        $user = User::factory()->admin()->create();
        Device::factory()->count(20)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/devices?perPage=5')
            ->assertStatus(200)
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonCount(5, 'data');
    }

    // ── Ports ────────────────────────────────────────────────

    public function testAdminCanListPorts(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Port::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ports')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testPortFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create([
            'ifName' => 'GigabitEthernet0/1',
            'ifAlias' => 'Uplink to core',
            'ifOperStatus' => 'up',
            'ifAdminStatus' => 'up',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/ports/{$port->port_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'GigabitEthernet0/1')
            ->assertJsonPath('data.attributes.alias', 'Uplink to core')
            ->assertJsonPath('data.attributes.operationalStatus', 'up');
    }

    public function testPortSearchByIfName(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Port::factory()->for($device)->create(['ifName' => 'GigabitEthernet0/1']);
        Port::factory()->for($device)->create(['ifName' => 'Loopback0']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ports?search=Gigabit')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ── Users ────────────────────────────────────────────────

    public function testAdminCanListAllUsers(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, $response->json('meta.total')); // admin + 2 users (at minimum)
    }

    public function testRegularUserCannotAccessUsers(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Sanctum::actingAs($user);

        // UserPolicy::allowRestify requires isAdmin()
        $this->getJson('/api/v1/users')
            ->assertStatus(403);
    }

    public function testUserFieldsArePresent(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'testadmin',
            'realname' => 'Test Admin',
            'email' => 'admin@example.com',
        ]);
        Sanctum::actingAs($admin);

        $this->getJson("/api/v1/users/{$admin->user_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.username', 'testadmin')
            ->assertJsonPath('data.attributes.realName', 'Test Admin')
            ->assertJsonPath('data.attributes.email', 'admin@example.com');
    }

    public function testPasswordIsNeverExposed(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/v1/users/{$admin->user_id}");

        $response->assertStatus(200);
        $this->assertArrayNotHasKey('password', $response->json('data.attributes'));
    }

    // ── Profile ──────────────────────────────────────────────

    public function testProfileReturnsAuthenticatedUser(): void
    {
        $user = User::factory()->admin()->create(['username' => 'myuser']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/profile')
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.username', 'myuser');
    }

    // ── Read-only enforcement ────────────────────────────────

    public function testDevicesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        // DevicePolicy::create() returns false for non-admin users
        $this->postJsonApi('/api/v1/devices', [
            'hostname' => 'new-device.example.com',
        ])->assertStatus(403);
    }

    public function testPortsCannotBeCreatedByReadOnlyUser(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        // PortPolicy::create() returns false
        $this->postJsonApi('/api/v1/ports', [
            'name' => 'eth0',
        ])->assertStatus(403);
    }

    // ── Alert Templates ───────────────────────────────────────

    public function testAdminCanListAlertTemplates(): void
    {
        $user = User::factory()->admin()->create();
        $existing = AlertTemplate::count();
        AlertTemplate::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-templates')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', $existing + 3);
    }

    public function testAdminCanShowAlertTemplate(): void
    {
        $user = User::factory()->admin()->create();
        $template = AlertTemplate::factory()->create(['name' => 'Test Template']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-templates/{$template->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Test Template');
    }

    public function testAlertTemplateFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $template = AlertTemplate::factory()->create([
            'name' => 'Critical Alert',
            'template' => '<b>Alert!</b>',
            'title' => 'Alert: {{ $title }}',
            'title_rec' => 'Recovered: {{ $title }}',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-templates/{$template->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Critical Alert')
            ->assertJsonPath('data.attributes.template', '<b>Alert!</b>')
            ->assertJsonPath('data.attributes.title', 'Alert: {{ $title }}')
            ->assertJsonPath('data.attributes.recoveryTitle', 'Recovered: {{ $title }}');
    }

    public function testAdminCanCreateAlertTemplate(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-templates', [
            'name' => 'New Template',
            'template' => '<p>{{ $alert->title }}</p>',
        ])->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'New Template');
    }

    public function testAdminCanUpdateAlertTemplate(): void
    {
        $user = User::factory()->admin()->create();
        $template = AlertTemplate::factory()->create(['name' => 'Old Name']);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/alert-templates/{$template->id}", [
            'name' => 'Updated Name',
            'template' => $template->template,
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Updated Name');
    }

    public function testAdminCanDeleteAlertTemplate(): void
    {
        $user = User::factory()->admin()->create();
        $template = AlertTemplate::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/alert-templates/{$template->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('alert_templates', ['id' => $template->id]);
    }

    public function testReadOnlyUserCannotCreateAlertTemplate(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-templates', [
            'name' => 'Forbidden',
            'template' => 'test',
        ])->assertStatus(403);
    }

    public function testAlertTemplateSearchByName(): void
    {
        $user = User::factory()->admin()->create();
        AlertTemplate::factory()->create(['name' => 'Critical Alerts']);
        AlertTemplate::factory()->create(['name' => 'Warning Notices']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-templates?search=Critical')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ── Alert Rules ─────────────────────────────────────────

    public function testAdminCanListAlertRules(): void
    {
        $user = User::factory()->admin()->create();
        AlertRule::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowAlertRule(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create(['name' => 'Device Down']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-rules/{$rule->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Device Down');
    }

    public function testAlertRuleFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create([
            'name' => 'Port Down',
            'severity' => 'critical',
            'disabled' => 0,
            'notes' => 'Check port status',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-rules/{$rule->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Port Down')
            ->assertJsonPath('data.attributes.severity', 'critical')
            ->assertJsonPath('data.attributes.notes', 'Check port status');
    }

    public function testAdminCanUpdateAlertRuleSeverity(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create([
            'name' => 'Test Rule',
            'severity' => 'warning',
            'disabled' => 0,
        ]);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/alert-rules/{$rule->id}", [
            'name' => 'Test Rule',
            'severity' => 'critical',
            'isEnabled' => 0,
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.severity', 'critical');
    }

    public function testAdminCanDeleteAlertRule(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/alert-rules/{$rule->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('alert_rules', ['id' => $rule->id]);
    }

    public function testReadOnlyUserCannotModifyAlertRule(): void
    {
        $user = User::factory()->read()->create();
        $rule = AlertRule::factory()->create();
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/alert-rules/{$rule->id}", [
            'name' => 'Hacked',
            'severity' => 'ok',
            'isEnabled' => 0,
        ])->assertStatus(403);
    }

    public function testAlertRuleSearchByName(): void
    {
        $user = User::factory()->admin()->create();
        AlertRule::factory()->create(['name' => 'Device Down']);
        AlertRule::factory()->create(['name' => 'Port Errors']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules?search=Device')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ── Device Groups ───────────────────────────────────────

    public function testAdminCanListDeviceGroups(): void
    {
        $user = User::factory()->admin()->create();
        DeviceGroup::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/device-groups')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowDeviceGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = DeviceGroup::factory()->create(['name' => 'core-routers']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/device-groups/{$group->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'core-routers');
    }

    public function testDeviceGroupFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $group = DeviceGroup::factory()->create([
            'name' => 'edge-switches',
            'desc' => 'All edge switches',
            'type' => 'static',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/device-groups/{$group->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'edge-switches')
            ->assertJsonPath('data.attributes.description', 'All edge switches')
            ->assertJsonPath('data.attributes.category', 'static');
    }

    public function testAdminCanCreateDeviceGroup(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/device-groups', [
            'name' => 'new-group',
            'description' => 'A new group',
            'category' => 'static',
        ])->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'new-group');
    }

    public function testAdminCanUpdateDeviceGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = DeviceGroup::factory()->create(['name' => 'old-name']);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/device-groups/{$group->id}", [
            'name' => 'new-name',
            'description' => $group->desc,
            'category' => $group->type,
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'new-name');
    }

    public function testAdminCanDeleteDeviceGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = DeviceGroup::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/device-groups/{$group->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('device_groups', ['id' => $group->id]);
    }

    public function testReadOnlyUserCannotCreateDeviceGroup(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/device-groups', [
            'name' => 'forbidden-group',
            'category' => 'static',
        ])->assertStatus(403);
    }

    public function testDeviceGroupSearchByName(): void
    {
        $user = User::factory()->admin()->create();
        DeviceGroup::factory()->create(['name' => 'core-routers']);
        DeviceGroup::factory()->create(['name' => 'edge-switches']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/device-groups?search=core')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ── Response structure ───────────────────────────────────

    public function testIndexResponseHasCorrectStructure(): void
    {
        $user = User::factory()->admin()->create();
        Device::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/devices')
            ->assertStatus(200)
            ->assertJsonStructure([
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
                'links' => ['first', 'next', 'path', 'prev'],
                'data' => [
                    '*' => ['id', 'type', 'attributes'],
                ],
            ]);
    }

    public function testShowResponseHasCorrectStructure(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/devices/{$device->device_id}")
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'type', 'attributes'],
            ]);
    }

    // ── Custom role with partial permissions ─────────────────

    private function createAlertTemplateReaderUser(): User
    {
        Permission::findOrCreate('api.access');
        Permission::findOrCreate('alert-template.view');
        Permission::findOrCreate('alert-template.viewAny');

        $role = Role::findOrCreate('alert-template-reader');
        $role->givePermissionTo(['api.access', 'alert-template.view', 'alert-template.viewAny']);

        $user = User::factory()->create();
        $user->assignRole('alert-template-reader');

        return $user;
    }

    public function testCustomRoleCanReadAlertTemplates(): void
    {
        $user = $this->createAlertTemplateReaderUser();
        $existing = AlertTemplate::count();
        AlertTemplate::factory()->count(2)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-templates')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', $existing + 2);
    }

    public function testCustomRoleCanShowSingleAlertTemplate(): void
    {
        $user = $this->createAlertTemplateReaderUser();
        $template = AlertTemplate::factory()->create(['name' => 'Visible Template']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-templates/{$template->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Visible Template');
    }

    public function testCustomRoleCannotCreateAlertTemplates(): void
    {
        $user = $this->createAlertTemplateReaderUser();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-templates', [
            'name' => 'Should Fail',
            'template' => 'body',
        ])->assertStatus(403);
    }

    public function testCustomRoleCannotAccessDevices(): void
    {
        $user = $this->createAlertTemplateReaderUser();
        Device::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/devices')
            ->assertStatus(403);
    }

    public function testCustomRoleCannotAccessAlertRules(): void
    {
        $user = $this->createAlertTemplateReaderUser();
        AlertRule::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(403);
    }

    // ── Locations ─────────────────────────────────────────────

    public function testAdminCanListLocations(): void
    {
        $user = User::factory()->admin()->create();
        Location::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/locations')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowLocation(): void
    {
        $user = User::factory()->admin()->create();
        $location = Location::factory()->create(['location' => 'Server Room A']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/locations/{$location->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Server Room A');
    }

    public function testLocationFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $location = Location::factory()->withCoordinates()->create([
            'location' => 'DC East',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/locations/{$location->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'DC East')
            ->assertJsonStructure(['data' => ['attributes' => ['name', 'latitude', 'longitude']]]);
    }

    public function testAdminCanCreateLocation(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/locations', [
            'name' => 'New Data Center',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
        ])->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'New Data Center');
    }

    public function testAdminCanUpdateLocation(): void
    {
        $user = User::factory()->admin()->create();
        $location = Location::factory()->create(['location' => 'Old Name']);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/locations/{$location->id}", [
            'name' => 'Updated Name',
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Updated Name');
    }

    public function testAdminCanDeleteLocation(): void
    {
        $user = User::factory()->admin()->create();
        $location = Location::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/locations/{$location->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function testReadOnlyUserCannotCreateLocation(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/locations', [
            'name' => 'Forbidden',
        ])->assertStatus(403);
    }

    public function testLocationSearchByName(): void
    {
        $user = User::factory()->admin()->create();
        Location::factory()->create(['location' => 'Data Center East']);
        Location::factory()->create(['location' => 'Office West']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/locations?search=Data+Center')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ── Port Groups ───────────────────────────────────────────

    public function testAdminCanListPortGroups(): void
    {
        $user = User::factory()->admin()->create();
        PortGroup::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-groups')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowPortGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = PortGroup::factory()->create(['name' => 'uplinks']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/port-groups/{$group->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'uplinks');
    }

    public function testAdminCanCreatePortGroup(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-groups', [
            'name' => 'new-port-group',
            'description' => 'A new group',
        ])->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'new-port-group');
    }

    public function testAdminCanUpdatePortGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = PortGroup::factory()->create(['name' => 'old-name']);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/port-groups/{$group->id}", [
            'name' => 'new-name',
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'new-name');
    }

    public function testAdminCanDeletePortGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = PortGroup::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/port-groups/{$group->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('port_groups', ['id' => $group->id]);
    }

    public function testReadOnlyUserCannotCreatePortGroup(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-groups', [
            'name' => 'forbidden',
        ])->assertStatus(403);
    }

    // ── Alert Schedules ───────────────────────────────────────

    public function testAdminCanListAlertSchedules(): void
    {
        $user = User::factory()->admin()->create();
        AlertSchedule::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-schedules')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowAlertSchedule(): void
    {
        $user = User::factory()->admin()->create();
        $schedule = AlertSchedule::factory()->create(['title' => 'Weekend Maintenance']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-schedules/{$schedule->schedule_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Weekend Maintenance');
    }

    public function testAlertScheduleFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $schedule = AlertSchedule::factory()->create([
            'title' => 'Planned Outage',
            'notes' => 'Router upgrade',
            'recurring' => 0,
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-schedules/{$schedule->schedule_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.title', 'Planned Outage')
            ->assertJsonPath('data.attributes.notes', 'Router upgrade');
    }

    public function testAdminCanDeleteAlertSchedule(): void
    {
        $user = User::factory()->admin()->create();
        $schedule = AlertSchedule::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/alert-schedules/{$schedule->schedule_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('alert_schedule', ['schedule_id' => $schedule->schedule_id]);
    }

    public function testReadOnlyUserCannotCreateAlertSchedule(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-schedules', [
            'title' => 'Forbidden',
        ])->assertStatus(403);
    }

    // ── Alert Transports ──────────────────────────────────────

    public function testAdminCanListAlertTransports(): void
    {
        $user = User::factory()->admin()->create();
        AlertTransport::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-transports')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowAlertTransport(): void
    {
        $user = User::factory()->admin()->create();
        $transport = AlertTransport::factory()->create(['transport_name' => 'Slack Alerts']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-transports/{$transport->transport_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Slack Alerts');
    }

    public function testAlertTransportFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $transport = AlertTransport::factory()->create([
            'transport_name' => 'Email Alert',
            'transport_type' => 'mail',
            'is_default' => 1,
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alert-transports/{$transport->transport_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Email Alert')
            ->assertJsonPath('data.attributes.category', 'mail')
            ->assertJsonPath('data.attributes.isDefault', true);
    }

    public function testAdminCanDeleteAlertTransport(): void
    {
        $user = User::factory()->admin()->create();
        $transport = AlertTransport::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/alert-transports/{$transport->transport_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('alert_transports', ['transport_id' => $transport->transport_id]);
    }

    public function testReadOnlyUserCannotCreateAlertTransport(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-transports', [
            'name' => 'Forbidden',
            'category' => 'mail',
        ])->assertStatus(403);
    }

    // ── Service Templates ─────────────────────────────────────

    public function testAdminCanListServiceTemplates(): void
    {
        $user = User::factory()->admin()->create();
        ServiceTemplate::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/service-templates')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowServiceTemplate(): void
    {
        $user = User::factory()->admin()->create();
        $template = ServiceTemplate::factory()->create(['name' => 'HTTP Check']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/service-templates/{$template->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'HTTP Check');
    }

    public function testServiceTemplateFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $template = ServiceTemplate::factory()->create([
            'name' => 'DNS Monitor',
            'check' => 'dns',
            'type' => 'static',
            'desc' => 'Monitor DNS servers',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/service-templates/{$template->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'DNS Monitor')
            ->assertJsonPath('data.attributes.check', 'dns')
            ->assertJsonPath('data.attributes.category', 'static')
            ->assertJsonPath('data.attributes.description', 'Monitor DNS servers');
    }

    public function testAdminCanDeleteServiceTemplate(): void
    {
        $user = User::factory()->admin()->create();
        $template = ServiceTemplate::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/service-templates/{$template->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('service_templates', ['id' => $template->id]);
    }

    public function testReadOnlyUserCannotCreateServiceTemplate(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/service-templates', [
            'name' => 'Forbidden',
            'check' => 'http',
            'category' => 'static',
        ])->assertStatus(403);
    }

    // ── Poller Groups ─────────────────────────────────────────

    public function testAdminCanListPollerGroups(): void
    {
        $user = User::factory()->admin()->create();
        PollerGroup::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/poller-groups')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowPollerGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = PollerGroup::factory()->create(['group_name' => 'EU Pollers']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/poller-groups/{$group->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'EU Pollers');
    }

    public function testAdminCanCreatePollerGroup(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/poller-groups', [
            'name' => 'US East Pollers',
            'description' => 'Pollers in US East region',
        ])->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'US East Pollers');
    }

    public function testAdminCanUpdatePollerGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = PollerGroup::factory()->create(['group_name' => 'Old Name']);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/poller-groups/{$group->id}", [
            'name' => 'New Name',
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'New Name');
    }

    public function testAdminCanDeletePollerGroup(): void
    {
        $user = User::factory()->admin()->create();
        $group = PollerGroup::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/poller-groups/{$group->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('poller_groups', ['id' => $group->id]);
    }

    public function testReadOnlyUserCannotCreatePollerGroup(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/poller-groups', [
            'name' => 'Forbidden',
        ])->assertStatus(403);
    }

    public function testPollerGroupSearchByName(): void
    {
        $user = User::factory()->admin()->create();
        PollerGroup::factory()->create(['group_name' => 'EU Pollers']);
        PollerGroup::factory()->create(['group_name' => 'US Pollers']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/poller-groups?search=EU')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    // ── Alerts ────────────────────────────────────────────────

    public function testAdminCanListAlerts(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Alert::factory()->count(3)->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alerts')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowAlert(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        // Alerts are engine-managed; the FK lives on the `device` relationship
        // which is only serialised when explicitly requested via ?related=device.
        $this->getJson("/api/v1/alerts/{$alert->id}?related=device")
            ->assertStatus(200)
            ->assertJsonPath('data.relationships.device.id', (string) $device->device_id);
    }

    public function testAlertFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->create([
            'device_id' => $device->device_id,
            'state' => 1,
            'note' => 'Test note',
        ]);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/alerts/{$alert->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.state', 1)
            ->assertJsonPath('data.attributes.note', 'Test note')
            ->assertJsonStructure(['data' => ['attributes' => [ 'state', 'note', 'createdAt']]]);
    }

    public function testAdminCanAcknowledgeAlert(): void
    {
        // Alerts are engine-managed; direct PUT is route-blocked with 405.
        // State changes happen via the acknowledge action (see
        // testAdminCanAcknowledgeViaAction).
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->create(['device_id' => $device->device_id, 'state' => 1]);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/alerts/{$alert->id}", [
            'state' => 2,
            'note' => 'Acknowledged via API',
        ])->assertStatus(405);
    }

    public function testAdminCanUnmuteAlert(): void
    {
        // Alerts are engine-managed; direct PUT is route-blocked with 405.
        // State changes happen via the unmute action (see
        // testAdminCanUnmuteViaAction).
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->acknowledged()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/alerts/{$alert->id}", [
            'state' => 1,
        ])->assertStatus(405);
    }

    public function testReadOnlyUserCannotUpdateAlert(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/alerts/{$alert->id}", [
            'state' => 2,
        ])->assertStatus(405);
    }

    public function testAlertsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alerts', [
            'device_id' => 1,
            'rule_id' => 1,
            'state' => 1,
        ])->assertStatus(405);
    }

    public function testAdminCanAcknowledgeViaAction(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->create(['device_id' => $device->device_id, 'state' => 1]);
        Sanctum::actingAs($user);

        $this->postJsonApi("/api/v1/alerts/{$alert->id}/actions?action=acknowledge", [
            'note' => 'Looking into it',
        ])->assertStatus(200)
            ->assertJsonPath('data.0.state', 2);

        $this->assertDatabaseHas('alerts', ['id' => $alert->id, 'state' => 2]);
    }

    public function testAdminCanUnmuteViaAction(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $alert = Alert::factory()->acknowledged()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->postJsonApi("/api/v1/alerts/{$alert->id}/actions?action=unmute", [
            'note' => 'Re-alerting',
        ])->assertStatus(200)
            ->assertJsonPath('data.0.state', 1);

        $this->assertDatabaseHas('alerts', ['id' => $alert->id, 'state' => 1]);
    }

    // ── Sensors ─────────────────────────────────────────────

    public function testAdminCanListSensors(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sensor::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/sensors')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowSensor(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $sensor = Sensor::factory()->for($device)->create(['sensor_descr' => 'CPU Temp']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/sensors/{$sensor->sensor_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.description', 'CPU Temp');
    }

    public function testSensorFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sensor::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/sensors')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'class', 'description', 'current',
                'limit', 'warningLimit', 'lowLimit', 'lowWarningLimit',
            ]]]]);
    }

    public function testSensorsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/sensors', ['class' => 'temperature'])
            ->assertStatus(403);
    }

    // ── Processors ──────────────────────────────────────────

    public function testAdminCanListProcessors(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Processor::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/processors')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testAdminCanShowProcessor(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $proc = Processor::factory()->for($device)->create(['processor_descr' => 'CPU 0']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/processors/{$proc->processor_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.description', 'CPU 0');
    }

    public function testProcessorFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Processor::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/processors')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'category', 'description', 'usage',
            ]]]]);
    }

    public function testProcessorsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/processors', ['category' => 'hr'])
            ->assertStatus(403);
    }

    // ── Mempools ────────────────────────────────────────────

    public function testAdminCanListMempools(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Mempool::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/memory-pools')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testAdminCanShowMempool(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $mempool = Mempool::factory()->for($device)->create(['mempool_descr' => 'Physical Memory']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/memory-pools/{$mempool->mempool_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.description', 'Physical Memory');
    }

    public function testMempoolFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Mempool::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/memory-pools')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'category', 'description', 'percentage',
                'used', 'free', 'total',
            ]]]]);
    }

    public function testMempoolsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/memory-pools', ['category' => 'hrstorage'])
            ->assertStatus(403);
    }

    // ── Storage ─────────────────────────────────────────────

    public function testAdminCanListStorage(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Storage::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/storage')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testAdminCanShowStorage(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $storage = Storage::factory()->for($device)->create(['storage_descr' => '/home']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/storage/{$storage->storage_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.description', '/home');
    }

    public function testStorageFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Storage::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/storage')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'category', 'description', 'size',
                'used', 'free', 'percentage',
            ]]]]);
    }

    public function testStorageCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/storage', ['category' => 'dsk'])
            ->assertStatus(403);
    }

    // ── Services ────────────────────────────────────────────

    public function testAdminCanListServices(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Service::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/services')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testAdminCanShowService(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $service = Service::factory()->for($device)->create(['service_type' => 'http']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/services/{$service->service_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.category', 'http');
    }

    public function testServiceFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Service::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/services')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'category', 'name', 'description',
                'status', 'isIgnored', 'isEnabled',
            ]]]]);
    }

    public function testAdminCanCreateService(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/services', [
            'device_id' => $device->device_id,
            'category' => 'http',
            'name' => 'Web Server',
            'description' => 'HTTP check',
            'ip' => '10.0.0.1',
        ])->assertStatus(201);

        $this->assertDatabaseHas('services', ['service_name' => 'Web Server']);
    }

    public function testAdminCanDeleteService(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $service = Service::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/services/{$service->service_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('services', ['service_id' => $service->service_id]);
    }

    public function testReadOnlyUserCannotCreateService(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/services', [
            'category' => 'http',
        ])->assertStatus(403);
    }

    // ── Components ──────────────────────────────────────────

    public function testAdminCanListComponents(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Component::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/components')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowComponent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $component = Component::factory()->for($device)->create(['label' => 'Fan 1']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/components/{$component->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.label', 'Fan 1');
    }

    public function testComponentFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Component::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/components')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'category', 'label', 'status', 'isEnabled', 'isIgnored', 'error',
            ]]]]);
    }

    public function testComponentsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/components', ['category' => 'fan'])
            ->assertStatus(403);
    }

    // ── Applications ────────────────────────────────────────

    public function testAdminCanListApplications(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Application::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/applications')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testAdminCanShowApplication(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $app = Application::factory()->for($device)->create(['app_type' => 'mysql']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/applications/{$app->app_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.category', 'mysql');
    }

    public function testApplicationFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Application::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/applications')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'category', 'instance', 'status', 'state',
            ]]]]);
    }

    public function testApplicationsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/applications', ['category' => 'mysql'])
            ->assertStatus(403);
    }

    // ── BGP Peers ───────────────────────────────────────────

    public function testAdminCanListBgpPeers(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        BgpPeer::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/bgp-peers')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowBgpPeer(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $peer = BgpPeer::factory()->for($device)->create(['bgpPeerState' => 'established']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/bgp-peers/{$peer->bgpPeer_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.state', 'established');
    }

    public function testBgpPeerFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        BgpPeer::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/bgp-peers')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'identifier', 'remoteAs',
                'state', 'adminStatus', 'localAddress', 'remoteAddress',
            ]]]]);
    }

    public function testBgpPeersCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/bgp-peers', ['remoteAs' => 65000])
            ->assertStatus(403);
    }

    // ── Eventlog ────────────────────────────────────────────

    public function testAdminCanListEventlogs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Eventlog::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/eventlogs');
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, $response->json('meta.total'));
    }

    public function testAdminCanShowEventlog(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $log = Eventlog::factory()->for($device)->create(['message' => 'Interface up']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/eventlogs/{$log->event_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.message', 'Interface up');
    }

    public function testEventlogFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Eventlog::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/eventlogs')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'createdAt', 'message', 'category', 'severity',
            ]]]]);
    }

    public function testEventlogsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/eventlogs', ['message' => 'test'])
            ->assertStatus(403);
    }

    // ── Syslog ──────────────────────────────────────────────

    public function testAdminCanListSyslogs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Syslog::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/syslogs')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowSyslog(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $log = Syslog::factory()->for($device)->create(['msg' => 'kernel: test message']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/syslogs/{$log->seq}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.message', 'kernel: test message');
    }

    public function testSyslogFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Syslog::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/syslogs')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'facility', 'priority', 'level', 'tag', 'program', 'message',
            ]]]]);
    }

    public function testSyslogsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/syslogs', ['message' => 'test'])
            ->assertStatus(403);
    }

    // ── Alert Log ───────────────────────────────────────────

    public function testAdminCanListAlertLogs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        AlertLog::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-logs')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testAdminCanShowAlertLog(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $log = AlertLog::factory()->for($device)->create();
        Sanctum::actingAs($user);

        // Alert logs are engine-managed; the FK lives on the `device`
        // relationship which is only serialised when explicitly requested.
        $this->getJson("/api/v1/alert-logs/{$log->id}?related=device")
            ->assertStatus(200)
            ->assertJsonPath('data.relationships.device.id', (string) $device->device_id);
    }

    public function testAlertLogFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        AlertLog::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-logs')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'state', 'createdAt',
            ]]]]);
    }

    public function testAlertLogsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-logs', ['state' => 1])
            ->assertStatus(403);
    }

    // ── Auth Log ────────────────────────────────────────────

    public function testAdminCanListAuthLogs(): void
    {
        $user = User::factory()->admin()->create();
        AuthLog::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth-logs');
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, $response->json('meta.total'));
    }

    public function testAdminCanShowAuthLog(): void
    {
        $user = User::factory()->admin()->create();
        $log = AuthLog::factory()->create(['user' => 'admin', 'result' => 'Logged In']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/auth-logs/{$log->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.user', 'admin')
            ->assertJsonPath('data.attributes.result', 'Logged In');
    }

    public function testAuthLogFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        AuthLog::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth-logs')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [
                'createdAt', 'user', 'address', 'result',
            ]]]]);
    }

    public function testAuthLogsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/auth-logs', ['user' => 'test'])
            ->assertStatus(403);
    }

    // ── Bills ───────────────────────────────────────────────

    public function testAdminCanListBills(): void
    {
        $user = User::factory()->admin()->create();
        Bill::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/bills')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowBill(): void
    {
        $user = User::factory()->admin()->create();
        $bill = Bill::factory()->create(['bill_name' => 'Transit Link']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/bills/{$bill->bill_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Transit Link');
    }

    public function testBillFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        Bill::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/bills')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [
                'name', 'category', 'rate95th', 'totalData',
                'rateAverage', 'updatedAt',
            ]]]]);
    }

    public function testAdminCanCreateBill(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/bills', [
            'name' => 'New Transit',
            'category' => 'quota',
        ])->assertStatus(201);

        $this->assertDatabaseHas('bills', ['bill_name' => 'New Transit']);
    }

    public function testAdminCanDeleteBill(): void
    {
        $user = User::factory()->admin()->create();
        $bill = Bill::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/bills/{$bill->bill_id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('bills', ['bill_id' => $bill->bill_id]);
    }

    public function testReadOnlyUserCannotCreateBill(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/bills', [
            'name' => 'Forbidden',
        ])->assertStatus(403);
    }

    // ── Poller Clusters ─────────────────────────────────────

    public function testAdminCanListPollerClusters(): void
    {
        $user = User::factory()->admin()->create();
        PollerCluster::factory()->count(2)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/poller-clusters');
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, $response->json('meta.total'));
    }

    public function testAdminCanShowPollerCluster(): void
    {
        $user = User::factory()->admin()->create();
        $cluster = PollerCluster::factory()->create(['poller_name' => 'poller-01']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/poller-clusters/{$cluster->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'poller-01');
    }

    public function testPollerClusterFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        PollerCluster::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/poller-clusters')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [
                'nodeId', 'name', 'version', 'groups',
                'updatedAt', 'isMaster',
            ]]]]);
    }

    public function testPollerClustersCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/poller-clusters', ['name' => 'test'])
            ->assertStatus(403);
    }

    // ── Inventory (EntPhysical) ─────────────────────────────

    public function testAdminCanListEntPhysicals(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        EntPhysical::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/inventory')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testAdminCanShowEntPhysical(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $entity = EntPhysical::factory()->for($device)->create(['entPhysicalName' => 'Chassis']);
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/inventory/{$entity->entPhysical_id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.name', 'Chassis');
    }

    public function testEntPhysicalFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        EntPhysical::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/inventory')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'index', 'description', 'class',
                'name', 'serialNumber', 'modelName',
            ]]]]);
    }

    public function testEntPhysicalsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/inventory', ['name' => 'test'])
            ->assertStatus(403);
    }

    // ── OSPF Instances ──────────────────────────────────────

    public function testAdminCanListOspfInstances(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        OspfInstance::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-instances')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testGlobalReadCanListOspfInstances(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        OspfInstance::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-instances')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testRegularUserCannotAccessOspfInstances(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        OspfInstance::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-instances')
            ->assertStatus(403);
    }

    public function testOspfInstanceFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        OspfInstance::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-instances')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'routerId', 'adminStatus', 'versionNumber',
            ]]]]);
    }

    public function testOspfInstancesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ospf-instances', ['routerId' => '1.1.1.1'])
            ->assertStatus(403);
    }

    // ── OSPF Areas ──────────────────────────────────────────

    public function testAdminCanListOspfAreas(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        OspfArea::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-areas')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testGlobalReadCanListOspfAreas(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        OspfArea::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-areas')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testRegularUserCannotAccessOspfAreas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        OspfArea::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-areas')
            ->assertStatus(403);
    }

    public function testOspfAreaFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        OspfArea::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-areas')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'areaId', 'spfRuns', 'status',
            ]]]]);
    }

    public function testOspfAreasCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ospf-areas', ['areaId' => '0.0.0.0'])
            ->assertStatus(403);
    }

    // ── OSPF Neighbors ──────────────────────────────────────

    public function testAdminCanListOspfNbrs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        OspfNbr::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-neighbors')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessOspfNbrs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        OspfNbr::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-neighbors')
            ->assertStatus(403);
    }

    public function testOspfNbrFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        OspfNbr::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-neighbors')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'ipAddress', 'routerId', 'state',
            ]]]]);
    }

    public function testOspfNbrsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ospf-neighbors', ['ipAddress' => '10.0.0.1'])
            ->assertStatus(403);
    }

    // ── OSPF Ports ──────────────────────────────────────────

    public function testAdminCanListOspfPorts(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        OspfPort::factory()->count(3)->for($device)->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-ports')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessOspfPorts(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        OspfPort::factory()->for($device)->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-ports')
            ->assertStatus(403);
    }

    public function testOspfPortFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        OspfPort::factory()->for($device)->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospf-ports')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'interfaceIpAddress', 'areaId',
            ]]]]);
    }

    public function testOspfPortsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ospf-ports', ['interfaceIpAddress' => '10.0.0.1'])
            ->assertStatus(403);
    }

    // ── OSPFv3 Instances ─────────────────────────────────────

    public function testAdminCanListOspfv3Instances(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Ospfv3Instance::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-instances')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testGlobalReadCanListOspfv3Instances(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        Ospfv3Instance::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-instances')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testRegularUserCannotAccessOspfv3Instances(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Ospfv3Instance::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-instances')
            ->assertStatus(403);
    }

    public function testOspfv3InstanceFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Ospfv3Instance::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-instances')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'routerId', 'adminStatus', 'versionNumber',
            ]]]]);
    }

    public function testOspfv3InstancesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ospfv3-instances', ['routerId' => '1.1.1.1'])
            ->assertStatus(403);
    }

    // ── OSPFv3 Areas ────────────────────────────────────────

    public function testAdminCanListOspfv3Areas(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Ospfv3Area::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-areas')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testGlobalReadCanListOspfv3Areas(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        Ospfv3Area::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-areas')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testRegularUserCannotAccessOspfv3Areas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Ospfv3Area::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-areas')
            ->assertStatus(403);
    }

    public function testOspfv3AreaFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Ospfv3Area::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-areas')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'areaId', 'spfRuns', 'summary',
            ]]]]);
    }

    public function testOspfv3AreasCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ospfv3-areas', ['areaId' => 0])
            ->assertStatus(403);
    }

    // ── OSPFv3 Neighbors ────────────────────────────────────

    public function testAdminCanListOspfv3Nbrs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ospfv3Nbr::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-neighbors')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessOspfv3Nbrs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ospfv3Nbr::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-neighbors')
            ->assertStatus(403);
    }

    public function testOspfv3NbrFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ospfv3Nbr::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-neighbors')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'routerId', 'address', 'state',
            ]]]]);
    }

    public function testOspfv3NbrsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ospfv3-neighbors', ['routerId' => '1.1.1.1'])
            ->assertStatus(403);
    }

    // ── OSPFv3 Ports ────────────────────────────────────────

    public function testAdminCanListOspfv3Ports(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ospfv3Port::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-ports')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessOspfv3Ports(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ospfv3Port::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-ports')
            ->assertStatus(403);
    }

    public function testOspfv3PortFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ospfv3Port::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ospfv3-ports')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'areaId', 'state',
            ]]]]);
    }

    public function testOspfv3PortsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ospfv3-ports', ['interfaceIndex' => 1])
            ->assertStatus(403);
    }

    // ── ISIS Adjacencies ────────────────────────────────────

    public function testAdminCanListIsisAdjacencies(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        IsisAdjacency::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/isis-adjacencies')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testGlobalReadCanListIsisAdjacencies(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        IsisAdjacency::factory()->count(2)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/isis-adjacencies')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testRegularUserCannotAccessIsisAdjacencies(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        IsisAdjacency::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/isis-adjacencies')
            ->assertStatus(403);
    }

    public function testIsisAdjacencyFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        IsisAdjacency::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/isis-adjacencies')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'state', 'neighborSystemId', 'ipAddress',
            ]]]]);
    }

    public function testIsisAdjacenciesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/isis-adjacencies', ['state' => 'up'])
            ->assertStatus(403);
    }

    // ── VRFs ────────────────────────────────────────────────

    public function testAdminCanListVrfs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Vrf::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vrfs')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testGlobalReadCanListVrfs(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        Vrf::factory()->count(2)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vrfs')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testRegularUserCannotAccessVrfs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Vrf::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vrfs')
            ->assertStatus(403);
    }

    public function testVrfFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Vrf::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vrfs')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'oid', 'name',
            ]]]]);
    }

    public function testVrfsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/vrfs', ['name' => 'test'])
            ->assertStatus(403);
    }

    // ── VRF Lite ────────────────────────────────────────────

    public function testAdminCanListVrfLites(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        VrfLite::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vrf-lite')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessVrfLites(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        VrfLite::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vrf-lite')
            ->assertStatus(403);
    }

    public function testVrfLiteFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        VrfLite::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vrf-lite')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'contextName', 'name',
            ]]]]);
    }

    public function testVrfLitesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/vrf-lite', ['name' => 'test'])
            ->assertStatus(403);
    }

    // ── Routes ──────────────────────────────────────────────

    public function testAdminCanListRoutes(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Route::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/routes')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testGlobalReadCanListRoutes(): void
    {
        $user = User::factory()->read()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Route::factory()->count(2)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/routes')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    public function testRegularUserCannotAccessRoutes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Route::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/routes')
            ->assertStatus(403);
    }

    public function testRouteFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Route::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/routes')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'destination', 'nextHop',
                'protocol', 'category',
            ]]]]);
    }

    public function testRoutesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/routes', ['destination' => '10.0.0.0'])
            ->assertStatus(403);
    }

    // ── MPLS LSPs ───────────────────────────────────────────

    public function testAdminCanListMplsLsps(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsLsp::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-label-switched-paths')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessMplsLsps(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        MplsLsp::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-label-switched-paths')
            ->assertStatus(403);
    }

    public function testMplsLspFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsLsp::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-label-switched-paths')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'name', 'adminState', 'operationalState',
                'fromAddress', 'toAddress',
            ]]]]);
    }

    public function testMplsLspsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/mpls-label-switched-paths', ['name' => 'test'])
            ->assertStatus(403);
    }

    // ── MPLS LSP Paths ──────────────────────────────────────

    public function testAdminCanListMplsLspPaths(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsLspPath::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-label-switched-path-routes')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessMplsLspPaths(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        MplsLspPath::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-label-switched-path-routes')
            ->assertStatus(403);
    }

    public function testMplsLspPathFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsLspPath::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-label-switched-path-routes')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'category', 'adminState', 'operationalState',
            ]]]]);
    }

    public function testMplsLspPathsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/mpls-label-switched-path-routes', ['category' => 'primary'])
            ->assertStatus(403);
    }

    // ── MPLS SDPs ───────────────────────────────────────────

    public function testAdminCanListMplsSdps(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsSdp::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-distribution-points')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessMplsSdps(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        MplsSdp::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-distribution-points')
            ->assertStatus(403);
    }

    public function testMplsSdpFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsSdp::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-distribution-points')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'description', 'adminStatus', 'operationalStatus',
            ]]]]);
    }

    public function testMplsSdpsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/mpls-service-distribution-points', ['description' => 'test'])
            ->assertStatus(403);
    }

    // ── MPLS SDP Binds ──────────────────────────────────────

    public function testAdminCanListMplsSdpBinds(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsSdpBind::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-distribution-point-bindings')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessMplsSdpBinds(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        MplsSdpBind::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-distribution-point-bindings')
            ->assertStatus(403);
    }

    public function testMplsSdpBindFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsSdpBind::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-distribution-point-bindings')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'adminStatus', 'operationalStatus',
            ]]]]);
    }

    public function testMplsSdpBindsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/mpls-service-distribution-point-bindings', ['sdp_id' => 1])
            ->assertStatus(403);
    }

    // ── MPLS Services ───────────────────────────────────────

    public function testAdminCanListMplsServices(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsService::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-services')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessMplsServices(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        MplsService::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-services')
            ->assertStatus(403);
    }

    public function testMplsServiceFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsService::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-services')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'description', 'category', 'adminStatus', 'operationalStatus',
            ]]]]);
    }

    public function testMplsServicesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/mpls-services', ['description' => 'test'])
            ->assertStatus(403);
    }

    // ── MPLS SAPs ───────────────────────────────────────────

    public function testAdminCanListMplsSaps(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsSap::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-access-points')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessMplsSaps(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        MplsSap::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-access-points')
            ->assertStatus(403);
    }

    public function testMplsSapFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsSap::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-service-access-points')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'description', 'adminStatus', 'operationalStatus',
            ]]]]);
    }

    public function testMplsSapsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/mpls-service-access-points', ['description' => 'test'])
            ->assertStatus(403);
    }

    // ── MPLS Tunnel AR Hops ─────────────────────────────────

    public function testAdminCanListMplsTunnelArHops(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsTunnelArHop::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-tunnel-actual-route-hops')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessMplsTunnelArHops(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        MplsTunnelArHop::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-tunnel-actual-route-hops')
            ->assertStatus(403);
    }

    public function testMplsTunnelArHopFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsTunnelArHop::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-tunnel-actual-route-hops')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'ipv4Address', 'addressCategory',
            ]]]]);
    }

    public function testMplsTunnelArHopsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/mpls-tunnel-actual-route-hops', ['ipv4Address' => '10.0.0.1'])
            ->assertStatus(403);
    }

    // ── MPLS Tunnel C Hops ──────────────────────────────────

    public function testAdminCanListMplsTunnelCHops(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsTunnelCHop::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-tunnel-computed-hops')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessMplsTunnelCHops(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        MplsTunnelCHop::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-tunnel-computed-hops')
            ->assertStatus(403);
    }

    public function testMplsTunnelCHopFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        MplsTunnelCHop::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/mpls-tunnel-computed-hops')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'ipv4Address', 'addressCategory',
            ]]]]);
    }

    public function testMplsTunnelCHopsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/mpls-tunnel-computed-hops', ['ipv4Address' => '10.0.0.1'])
            ->assertStatus(403);
    }

    // ── IPv4 Addresses ──────────────────────────────────────

    public function testAdminCanListIpv4Addresses(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv4Address::factory()->count(3)->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-addresses')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessIpv4Addresses(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv4Address::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-addresses')
            ->assertStatus(403);
    }

    public function testIpv4AddressFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv4Address::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-addresses')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [
                'address', 'prefixLength',
            ]]]]);
    }

    public function testIpv4AddressesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ipv4-addresses', ['ipv4Address' => '10.0.0.1'])
            ->assertStatus(403);
    }

    // ── IPv6 Addresses ──────────────────────────────────────

    public function testAdminCanListIpv6Addresses(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv6Address::factory()->count(3)->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-addresses')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessIpv6Addresses(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv6Address::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-addresses')
            ->assertStatus(403);
    }

    public function testIpv6AddressFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv6Address::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-addresses')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [
                'address', 'compressedAddress', 'prefixLength',
            ]]]]);
    }

    public function testIpv6AddressesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ipv6-addresses', ['ipv6Address' => '::1'])
            ->assertStatus(403);
    }

    // ── VLANs ───────────────────────────────────────────────

    public function testAdminCanListVlans(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Vlan::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vlans')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessVlans(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Vlan::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vlans')
            ->assertStatus(403);
    }

    public function testVlanFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Vlan::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/vlans')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'vid', 'name', 'category',
            ]]]]);
    }

    public function testVlansCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/vlans', ['name' => 'test'])
            ->assertStatus(403);
    }

    // ── Links ───────────────────────────────────────────────

    public function testAdminCanListLinks(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Link::factory()->count(3)->create(['local_device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/links')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessLinks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Link::factory()->create(['local_device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/links')
            ->assertStatus(403);
    }

    public function testLinkFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Link::factory()->create(['local_device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/links')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'remoteHostname', 'remotePortName', 'protocol', 'isActive',
            ]]]]);
    }

    public function testLinksCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/links', ['remoteHostname' => 'test'])
            ->assertStatus(403);
    }

    // ── IPv4 Networks ───────────────────────────────────────

    public function testAdminCanListIpv4Networks(): void
    {
        $user = User::factory()->admin()->create();
        Ipv4Network::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-networks')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessIpv4Networks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Ipv4Network::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-networks')
            ->assertStatus(403);
    }

    public function testIpv4NetworkFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        Ipv4Network::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-networks')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [
                'network',
            ]]]]);
    }

    public function testIpv4NetworksCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ipv4-networks', ['network' => '10.0.0.0/8'])
            ->assertStatus(403);
    }

    // ── IPv6 Networks ───────────────────────────────────────

    public function testAdminCanListIpv6Networks(): void
    {
        $user = User::factory()->admin()->create();
        Ipv6Network::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-networks')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessIpv6Networks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        Ipv6Network::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-networks')
            ->assertStatus(403);
    }

    public function testIpv6NetworkFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        Ipv6Network::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-networks')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [
                'network',
            ]]]]);
    }

    public function testIpv6NetworksCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ipv6-networks', ['network' => '2001:db8::/32'])
            ->assertStatus(403);
    }

    // ── IPv4 MAC (ARP) ─────────────────────────────────────

    public function testAdminCanListIpv4Macs(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv4Mac::factory()->count(3)->create(['port_id' => $port->port_id, 'device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-mac-addresses')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessIpv4Macs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv4Mac::factory()->create(['port_id' => $port->port_id, 'device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-mac-addresses')
            ->assertStatus(403);
    }

    public function testIpv4MacFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv4Mac::factory()->create(['port_id' => $port->port_id, 'device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv4-mac-addresses')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'macAddress', 'ipv4Address',
            ]]]]);
    }

    public function testIpv4MacsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ipv4-mac-addresses', ['macAddress' => 'aa:bb:cc:dd:ee:ff'])
            ->assertStatus(403);
    }

    // ── FDB (MAC Forwarding Table) ──────────────────────────

    public function testAdminCanListPortsFdb(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortsFdb::factory()->count(3)->create(['port_id' => $port->port_id, 'device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-forwarding-database-entries')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessPortsFdb(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortsFdb::factory()->create(['port_id' => $port->port_id, 'device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-forwarding-database-entries')
            ->assertStatus(403);
    }

    public function testPortsFdbFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortsFdb::factory()->create(['port_id' => $port->port_id, 'device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-forwarding-database-entries')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'macAddress',
            ]]]]);
    }

    public function testPortsFdbCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-forwarding-database-entries', ['macAddress' => 'aa:bb:cc:dd:ee:ff'])
            ->assertStatus(403);
    }

    // ── IPv6 Neighbor Discovery ─────────────────────────────

    public function testAdminCanListIpv6Nd(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv6Nd::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-neighbor-discovery')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessIpv6Nd(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv6Nd::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-neighbor-discovery')
            ->assertStatus(403);
    }

    public function testIpv6NdFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Ipv6Nd::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipv6-neighbor-discovery')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'macAddress', 'ipv6Address',
            ]]]]);
    }

    public function testIpv6NdCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ipv6-neighbor-discovery', ['ipv6Address' => '::1'])
            ->assertStatus(403);
    }

    // ── Port VLANs ──────────────────────────────────────────

    public function testAdminCanListPortVlans(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortVlan::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-vlans')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessPortVlans(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortVlan::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-vlans')
            ->assertStatus(403);
    }

    public function testPortVlanFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortVlan::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-vlans')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'vlan', 'state',
            ]]]]);
    }

    public function testPortVlansCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-vlans', ['vlan' => 100])
            ->assertStatus(403);
    }

    // ── Access Points ───────────────────────────────────────

    public function testAdminCanListAccessPoints(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        AccessPoint::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/access-points')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessAccessPoints(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        AccessPoint::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/access-points')
            ->assertStatus(403);
    }

    public function testAccessPointFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        AccessPoint::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/access-points')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'name', 'macAddress', 'channel', 'associatedClients',
            ]]]]);
    }

    public function testAccessPointsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/access-points', ['name' => 'test-ap'])
            ->assertStatus(403);
    }

    // ── Wireless Sensors ────────────────────────────────────

    public function testAdminCanListWirelessSensors(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        WirelessSensor::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wireless-sensors')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessWirelessSensors(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        WirelessSensor::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wireless-sensors')
            ->assertStatus(403);
    }

    public function testWirelessSensorFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        WirelessSensor::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/wireless-sensors')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'class', 'description', 'category',
            ]]]]);
    }

    public function testWirelessSensorsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/wireless-sensors', ['description' => 'test'])
            ->assertStatus(403);
    }

    // ── Transceivers ────────────────────────────────────────

    public function testAdminCanListTransceivers(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Transceiver::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/transceivers')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessTransceivers(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Transceiver::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/transceivers')
            ->assertStatus(403);
    }

    public function testTransceiverFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Transceiver::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/transceivers')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'vendor', 'model', 'serial',
            ]]]]);
    }

    public function testTransceiversCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/transceivers', ['vendor' => 'test'])
            ->assertStatus(403);
    }

    // ── Disk I/O ────────────────────────────────────────────

    public function testAdminCanListDiskIos(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        DiskIo::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/disk-io')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessDiskIos(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        DiskIo::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/disk-io')
            ->assertStatus(403);
    }

    public function testDiskIoFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        DiskIo::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/disk-io')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'index', 'description',
            ]]]]);
    }

    public function testDiskIosCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/disk-io', ['description' => 'test'])
            ->assertStatus(403);
    }

    // ── SLAs ────────────────────────────────────────────────

    public function testAdminCanListSlas(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sla::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/service-level-agreements')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessSlas(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Sla::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/service-level-agreements')
            ->assertStatus(403);
    }

    public function testSlaFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Sla::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/service-level-agreements')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'owner', 'tag', 'rttCategory', 'status',
            ]]]]);
    }

    public function testSlasCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/service-level-agreements', ['tag' => 'test'])
            ->assertStatus(403);
    }

    // ── Device Outages ──────────────────────────────────────

    public function testAdminCanListDeviceOutages(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        DeviceOutage::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/device-outages');
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, $response->json('meta.total'));
    }

    public function testRegularUserCannotAccessDeviceOutages(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        DeviceOutage::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/device-outages')
            ->assertStatus(403);
    }

    public function testDeviceOutageFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        DeviceOutage::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/device-outages')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'outageStartAt',
            ]]]]);
    }

    public function testDeviceOutagesCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/device-outages', ['outageStartAt' => 1000])
            ->assertStatus(403);
    }

    // ── Availability ────────────────────────────────────────

    public function testAdminCanListAvailability(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Availability::factory()->count(3)->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/availabilities');
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, $response->json('meta.total'));
    }

    public function testRegularUserCannotAccessAvailability(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Availability::factory()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/availabilities')
            ->assertStatus(403);
    }

    public function testAvailabilityFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Availability::factory()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/availabilities')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'duration', 'percentage',
            ]]]]);
    }

    public function testAvailabilityCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/availabilities', ['duration' => 86400])
            ->assertStatus(403);
    }

    // ── IPsec Tunnels ───────────────────────────────────────

    public function testAdminCanListIpsecTunnels(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        IpsecTunnel::factory()->count(3)->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipsec-tunnels')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessIpsecTunnels(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        IpsecTunnel::factory()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipsec-tunnels')
            ->assertStatus(403);
    }

    public function testIpsecTunnelFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        IpsecTunnel::factory()->create(['device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/ipsec-tunnels')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'name', 'peerAddress', 'localAddress', 'status',
            ]]]]);
    }

    public function testIpsecTunnelsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/ipsec-tunnels', ['name' => 'test'])
            ->assertStatus(403);
    }

    // ── Pseudowires ─────────────────────────────────────────

    public function testAdminCanListPseudowires(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Pseudowire::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/pseudowires')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessPseudowires(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Pseudowire::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/pseudowires')
            ->assertStatus(403);
    }

    public function testPseudowireFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        Pseudowire::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/pseudowires')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'category', 'description',
            ]]]]);
    }

    public function testPseudowiresCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/pseudowires', ['description' => 'test'])
            ->assertStatus(403);
    }

    // ── Port Stacking ───────────────────────────────────────

    public function testAdminCanListPortStacks(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        PortStack::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-stacks')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessPortStacks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        PortStack::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-stacks')
            ->assertStatus(403);
    }

    public function testPortStackFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        PortStack::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-stacks')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'highInterfaceIndex', 'lowInterfaceIndex', 'stackStatus',
            ]]]]);
    }

    public function testPortStacksCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-stacks', ['stackStatus' => 'active'])
            ->assertStatus(403);
    }

    // ── Port STP ────────────────────────────────────────────

    public function testAdminCanListPortStps(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortStp::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-spanning-trees')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessPortStps(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortStp::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-spanning-trees')
            ->assertStatus(403);
    }

    public function testPortStpFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortStp::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-spanning-trees')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'vlan', 'state', 'designatedRoot',
            ]]]]);
    }

    public function testPortStpsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-spanning-trees', ['state' => 'forwarding'])
            ->assertStatus(403);
    }

    // ── STP Instances ───────────────────────────────────────

    public function testAdminCanListStps(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Stp::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/spanning-trees')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessStps(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        Stp::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/spanning-trees')
            ->assertStatus(403);
    }

    public function testStpFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        Stp::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/spanning-trees')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'bridgeAddress', 'designatedRoot', 'priority',
            ]]]]);
    }

    public function testStpsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/spanning-trees', ['bridgeAddress' => 'aa:bb:cc:dd:ee:ff'])
            ->assertStatus(403);
    }

    // ── Port ADSL ───────────────────────────────────────────

    public function testAdminCanListPortAdsls(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortAdsl::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-adsl')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function testRegularUserCannotAccessPortAdsls(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortAdsl::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-adsl')
            ->assertStatus(403);
    }

    public function testPortAdslFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortAdsl::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-adsl')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'atucCurrentSnrMargin', 'atucChannelCurrentTransmitRate',
            ]]]]);
    }

    public function testPortAdslsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-adsl', ['lineCoding' => 'DMT'])
            ->assertStatus(403);
    }

    // ── Port VDSL ───────────────────────────────────────────

    public function testAdminCanListPortVdsls(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortVdsl::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-vdsl')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 1);
    }

    public function testRegularUserCannotAccessPortVdsls(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortVdsl::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-vdsl')
            ->assertStatus(403);
    }

    public function testPortVdslFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortVdsl::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-vdsl')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'attainableRateDownstream', 'actualDataRateReceive',
            ]]]]);
    }

    public function testPortVdslsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-vdsl', ['attainableRateDownstream' => 100000])
            ->assertStatus(403);
    }

    // ── Ports NAC ───────────────────────────────────────────

    public function testAdminCanListPortsNac(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortsNac::factory()->count(3)->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-network-access-controls')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessPortsNac(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortsNac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-network-access-controls')
            ->assertStatus(403);
    }

    public function testPortsNacFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortsNac::factory()->create(['device_id' => $device->device_id, 'port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-network-access-controls')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'username', 'macAddress', 'authorizationStatus',
            ]]]]);
    }

    public function testPortsNacCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-network-access-controls', ['username' => 'test'])
            ->assertStatus(403);
    }

    // ── Port Security ───────────────────────────────────────

    public function testAdminCanListPortSecurity(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        PortSecurity::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-securities')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessPortSecurity(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        PortSecurity::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-securities')
            ->assertStatus(403);
    }

    public function testPortSecurityFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        PortSecurity::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-securities')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'status', 'maxAddresses',
            ]]]]);
    }

    public function testPortSecurityCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-securities', ['isUp' => 'secureup'])
            ->assertStatus(403);
    }

    // ── Port Statistics ─────────────────────────────────────

    public function testAdminCanListPortStatistics(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortStatistic::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/port-statistics');
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('meta.total'));
    }

    public function testRegularUserCannotAccessPortStatistics(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortStatistic::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-statistics')
            ->assertStatus(403);
    }

    public function testPortStatisticFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        PortStatistic::factory()->create(['port_id' => $port->port_id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/port-statistics')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [
            ]]]]);
    }

    public function testPortStatisticsCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/port-statistics', ['port_id' => 1])
            ->assertStatus(403);
    }

    // ── CEF Switching ───────────────────────────────────────

    public function testAdminCanListCefSwitching(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        CefSwitching::factory()->count(3)->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/cef-switching')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testRegularUserCannotAccessCefSwitching(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $device = Device::factory()->create();
        CefSwitching::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/cef-switching')
            ->assertStatus(403);
    }

    public function testCefSwitchingFieldsArePresent(): void
    {
        $user = User::factory()->admin()->create();
        $device = Device::factory()->create();
        CefSwitching::factory()->for($device)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/cef-switching')
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [['attributes' => [ 'afi', 'path', 'drops', 'punts',
            ]]]]);
    }

    public function testCefSwitchingCannotBeCreatedViaApi(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/cef-switching', ['path' => 'receive'])
            ->assertStatus(403);
    }
}
