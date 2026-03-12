<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Http\Middleware\EnforceJsonApi;
use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Port;
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
            ->assertJsonPath('data.attributes.status', true);
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
            ->assertJsonPath('data.attributes.ifName', 'GigabitEthernet0/1')
            ->assertJsonPath('data.attributes.ifAlias', 'Uplink to core')
            ->assertJsonPath('data.attributes.ifOperStatus', 'up');
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

        $this->getJson('/api/v1/users')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3); // admin + 2 users
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
            ->assertJsonPath('data.attributes.realname', 'Test Admin')
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
            'ifName' => 'eth0',
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
            ->assertJsonPath('data.attributes.title_rec', 'Recovered: {{ $title }}');
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
            'disabled' => 0,
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
            'disabled' => 0,
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
            ->assertJsonPath('data.attributes.desc', 'All edge switches')
            ->assertJsonPath('data.attributes.type', 'static');
    }

    public function testAdminCanCreateDeviceGroup(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/device-groups', [
            'name' => 'new-group',
            'desc' => 'A new group',
            'type' => 'static',
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
            'desc' => $group->desc,
            'type' => $group->type,
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
            'type' => 'static',
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
}
