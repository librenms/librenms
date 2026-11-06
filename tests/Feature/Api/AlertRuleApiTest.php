<?php

namespace LibreNMS\Tests\Feature\Api;

use App\Facades\LibrenmsConfig;
use App\Http\Middleware\EnforceJsonApi;
use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\Device;
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
     * @return TestResponse<\Symfony\Component\HttpFoundation\Response>
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
     * @return TestResponse<\Symfony\Component\HttpFoundation\Response>
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
     * @return TestResponse<\Symfony\Component\HttpFoundation\Response>
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
            ->assertStatus(401)
            ->assertHeader('Content-Type', EnforceJsonApi::CONTENT_TYPE)
            ->assertJsonMissingPath('message')
            ->assertJsonPath('errors.0.status', '401')
            ->assertJsonPath('errors.0.code', 'unauthenticated');
    }

    public function testDisabledApiReturns404(): void
    {
        LibrenmsConfig::set('api.v1.enabled', false);

        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules')
            ->assertStatus(404)
            ->assertJsonPath('errors.0.status', '404')
            ->assertJsonPath('errors.0.code', 'not_found');
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
        $query = 'devices.status = 0';

        $response = $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'Device Unreachable',
            'severity' => 'critical',
            'isEnabled' => true,
            'procedure' => 'Page on-call',
            'notes' => 'Triggered when ICMP fails for 5m',
            'isScopeInverted' => false,
            'isConditionInverted' => false,
            'isMuted' => true,
            'sendsRecoveryAlerts' => false,
            'sendsAcknowledgementAlerts' => true,
            'overridesQuery' => true,
            'builder' => $builder,
            'query' => $query,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.attributes.name', 'Device Unreachable')
            ->assertJsonPath('data.attributes.severity', 'critical')
            ->assertJsonPath('data.attributes.isEnabled', true)
            ->assertJsonPath('data.attributes.procedure', 'Page on-call')
            ->assertJsonPath('data.attributes.notes', 'Triggered when ICMP fails for 5m')
            ->assertJsonPath('data.attributes.isScopeInverted', false)
            ->assertJsonPath('data.attributes.isConditionInverted', false)
            ->assertJsonPath('data.attributes.isMuted', true)
            ->assertJsonPath('data.attributes.sendsRecoveryAlerts', false)
            ->assertJsonPath('data.attributes.sendsAcknowledgementAlerts', true)
            ->assertJsonPath('data.attributes.overridesQuery', true)
            ->assertJsonPath('data.attributes.builder', $builder)
            ->assertJsonPath('data.attributes.query', $query);

        $created = AlertRule::where('name', 'Device Unreachable')->firstOrFail();
        $this->assertSame($builder, $created->builder);
        $this->assertEquals([
            'invert' => false,
            'mute' => true,
            'recovery' => false,
            'acknowledgement' => true,
            'options' => ['override_query' => true],
        ], $created->extra);
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
        ])->assertStatus(201)
            ->assertJsonPath('data.attributes.isMuted', false)
            ->assertJsonPath('data.attributes.isConditionInverted', false)
            ->assertJsonPath('data.attributes.sendsRecoveryAlerts', true)
            ->assertJsonPath('data.attributes.sendsAcknowledgementAlerts', true)
            ->assertJsonPath('data.attributes.overridesQuery', false);

        $created = AlertRule::where('name', 'Bare Minimum Rule')->firstOrFail();
        $this->assertSame([], $created->builder);
        $this->assertSame([], $created->extra);
        $this->assertSame('', $created->query);
    }

    public function testAlertRuleCreateRejectsMissingRequiredFields(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $response = $this->postJsonApi('/api/v1/alert-rules', [
            'notes' => 'no name, no severity',
        ]);

        // JSON:API error document: errors must be an array of error objects
        // with source pointers, not Laravel's field-keyed object
        $response->assertStatus(422)
            ->assertJsonMissingPath('message')
            ->assertJsonPath('errors.0.status', '422')
            ->assertJsonPath('errors.0.code', 'validation_failed');

        $pointers = array_column(array_column($response->json('errors'), 'source'), 'pointer');
        $this->assertContains('/name', $pointers);
        $this->assertContains('/severity', $pointers);
    }

    public function testShowUnknownAlertRuleReturns404(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/alert-rules/999999')
            ->assertStatus(404)
            ->assertJsonPath('errors.0.status', '404')
            ->assertJsonPath('errors.0.code', 'not_found');
    }

    public function testRawExtraAttributeIsIgnored(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        // extra is not an API field; the legacy pacing keys inside it
        // (delay/interval/count) were replaced by alert operations
        $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'Raw Extra',
            'severity' => 'warning',
            'isEnabled' => true,
            'extra' => ['mute' => true, 'count' => '-1', 'delay' => 300, 'interval' => 300],
        ])->assertStatus(201);

        $created = AlertRule::where('name', 'Raw Extra')->firstOrFail();
        $this->assertSame([], $created->extra);
    }

    public function testAlertRuleCreateRejectsNonBooleanFlagValues(): void
    {
        $user = User::factory()->admin()->create();
        Sanctum::actingAs($user);

        $this->postJsonApi('/api/v1/alert-rules', [
            'name' => 'Bad Flag Type',
            'severity' => 'warning',
            'isEnabled' => true,
            'isConditionInverted' => 'yes',
        ])->assertStatus(422);
    }

    public function testUpdatingFlagPreservesOtherExtraKeys(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create([
            'name' => 'Flag Merge',
            'severity' => 'warning',
            'extra' => ['invert' => true, 'options' => ['override_query' => true]],
        ]);
        Sanctum::actingAs($user);

        $this->putJsonApi("/api/v1/alert-rules/{$rule->id}", [
            'name' => 'Flag Merge',
            'severity' => 'warning',
            'isEnabled' => true,
            'isMuted' => true,
        ])->assertStatus(200)
            ->assertJsonPath('data.attributes.isMuted', true)
            ->assertJsonPath('data.attributes.isConditionInverted', true)
            ->assertJsonPath('data.attributes.overridesQuery', true);

        $this->assertEquals([
            'invert' => true,
            'options' => ['override_query' => true],
            'mute' => true,
        ], $rule->fresh()->extra);
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

    public function testDeletingAlertRuleCleansUpRelatedRecords(): void
    {
        $user = User::factory()->admin()->create();
        $rule = AlertRule::factory()->create();
        $device = Device::factory()->create();
        $rule->devices()->attach($device->device_id);
        Alert::factory()->create(['rule_id' => $rule->id, 'device_id' => $device->device_id]);
        AlertLog::factory()->create(['rule_id' => $rule->id, 'device_id' => $device->device_id]);
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/alert-rules/{$rule->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('alert_rules', ['id' => $rule->id]);
        $this->assertDatabaseMissing('alerts', ['rule_id' => $rule->id]);
        $this->assertDatabaseMissing('alert_log', ['rule_id' => $rule->id]);
        $this->assertDatabaseMissing('alert_device_map', ['rule_id' => $rule->id]);
    }

    public function testReadOnlyUserCannotDeleteAlertRule(): void
    {
        $user = User::factory()->read()->create();
        $rule = AlertRule::factory()->create();
        Sanctum::actingAs($user);

        $this->deleteJsonApi("/api/v1/alert-rules/{$rule->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('alert_rules', ['id' => $rule->id]);
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
