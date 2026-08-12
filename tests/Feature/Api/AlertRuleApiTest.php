<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Facades\LibrenmsConfig;
use App\Http\Middleware\EnforceJsonApi;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AlertRuleApiTest extends DBTestCase
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

        LibrenmsConfig::set('api.v1.enabled', true);
    }

    /**
     * Send a JSON:API POST request with the correct Content-Type.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
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
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
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
     *
     * @param  array<string, string>  $headers
     */
    protected function deleteJsonApi(string $uri, array $headers = []): TestResponse
    {
        return $this->json('DELETE', $uri, [], array_merge([
            'Accept' => EnforceJsonApi::CONTENT_TYPE,
        ], $headers));
    }

    /**
     * A user whose role can view alert rules but lacks the api.access permission.
     */
    protected function createUserWithoutApiAccess(): User
    {
        $role = Role::findOrCreate('alert-rule-reader');
        $role->givePermissionTo(Permission::findOrCreate('alert-rule.viewAny'));
        $role->givePermissionTo(Permission::findOrCreate('alert-rule.view'));

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    // ── Authentication & authorization ───────────────────────

    public function testUnauthenticatedRequestReturns401(): void
    {
        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(401);
    }

    public function testDisabledApiReturns404(): void
    {
        LibrenmsConfig::set('api.v1.enabled', false);

        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(404);
    }

    public function testUserWithoutApiAccessPermissionCannotAccessAlertRules(): void
    {
        $user = $this->createUserWithoutApiAccess();
        AlertRule::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(403);
    }

    public function testResponseHasJsonApiContentType(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(200)
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE);
    }

    // ── Read ─────────────────────────────────────────────────

    public function testAdminCanListAlertRules(): void
    {
        $user = User::factory()->admin()->create();
        AlertRule::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 3);
    }

    public function testGlobalReadCanListAlertRules(): void
    {
        $user = User::factory()->read()->create();
        AlertRule::factory()->count(2)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
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
            ->assertJsonPath('data.attributes.isEnabled', true)
            ->assertJsonPath('data.attributes.notes', 'Check port status');
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

    public function testAlertRuleMatchBySeverity(): void
    {
        $user = User::factory()->admin()->create();
        AlertRule::factory()->create(['severity' => 'critical']);
        AlertRule::factory()->create(['severity' => 'warning']);
        AlertRule::factory()->create(['severity' => 'warning']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules?severity=warning')
            ->assertStatus(200)
            ->assertJsonPath('meta.total', 2);
    }

    // ── Create ───────────────────────────────────────────────

    public function testAdminCanCreateAlertRule(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $builder = [
            'condition' => 'AND',
            'rules' => [['id' => 'devices.status', 'operator' => 'equal', 'value' => 0]],
        ];
        $extra = ['mute' => false, 'count' => '-1', 'delay' => 300, 'interval' => 300];
        $query = 'devices.status = 0';

        $response = $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'Device Unreachable',
            'severity' => 'critical',
            'isEnabled' => true,
            'procedure' => 'Page on-call',
            'notes' => 'Triggered when ICMP fails for 5m',
            'isInverted' => false,
            'builder' => $builder,
            'extra' => $extra,
            'query' => $query,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'Device Unreachable')
            ->assertJsonPath('data.attributes.severity', 'critical')
            ->assertJsonPath('data.attributes.isEnabled', true)
            ->assertJsonPath('data.attributes.procedure', 'Page on-call')
            ->assertJsonPath('data.attributes.notes', 'Triggered when ICMP fails for 5m')
            ->assertJsonPath('data.attributes.isInverted', false)
            ->assertJsonPath('data.attributes.builder', $builder)
            ->assertJsonPath('data.attributes.query', $query);

        $created = AlertRule::where('name', 'Device Unreachable')->firstOrFail();
        $this->assertSame($builder, $created->builder);
        $this->assertSame($extra, $created->extra);
        $this->assertSame($query, $created->query);
        $this->assertSame('Page on-call', $created->proc);
        $this->assertSame(0, (int) $created->disabled);
        $this->assertSame(0, (int) $created->invert_map);
    }

    public function testAlertRuleCreateUsesDefaultsForOptionalFields(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'Bare Minimum Rule',
            'severity' => 'warning',
            'isEnabled' => true,
        ])->assertStatus(201);

        $created = AlertRule::where('name', 'Bare Minimum Rule')->firstOrFail();
        $this->assertSame([], $created->builder);
        $this->assertSame([], $created->extra);
        $this->assertSame('', $created->query);
    }

    public function testAlertRuleCreateRejectsMissingRequiredFields(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-rules', [
            'notes' => 'no name, no severity',
        ])->assertStatus(422);
    }

    public function testAlertRuleCreateRejectsInvalidSeverity(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'Bad Severity',
            'severity' => 'catastrophic',
            'isEnabled' => true,
        ])->assertStatus(422);
    }

    public function testReadOnlyUserCannotCreateAlertRule(): void
    {
        $user = User::factory()->read()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'Should Be Rejected',
            'severity' => 'warning',
            'isEnabled' => true,
        ])->assertStatus(403);
    }

    // ── Update & delete ──────────────────────────────────────

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

    // ── Alert operation reference ────────────────────────────

    public function testAlertRuleCanReferenceAlertOperation(): void
    {
        $user = User::factory()->admin()->create();
        $op = AlertOperation::create(['name' => 'Op']);
        Sanctum::actingAs($user);

        $response = $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'With Operation',
            'severity' => 'warning',
            'isEnabled' => true,
            'alertOperationId' => $op->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.attributes.alertOperationId', $op->id);

        $rule = AlertRule::where('name', 'With Operation')->firstOrFail();
        $this->assertSame($op->id, $rule->alert_operation_id);
    }

    public function testAlertRuleRejectsUnknownAlertOperationId(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'Bad FK',
            'severity' => 'warning',
            'isEnabled' => true,
            'alertOperationId' => 999999,
        ])->assertStatus(422);
    }
}
