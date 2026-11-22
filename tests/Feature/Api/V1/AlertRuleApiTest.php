<?php

namespace LibreNMS\Tests\Feature\Api\V1;

use App\Facades\LibrenmsConfig;
use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AlertRuleApiTest extends DBTestCase
{
    use DatabaseTransactions;

    private const string URI = '/api/v1/alert-rules';

    private const array BUILDER = [
        'condition' => 'AND',
        'rules' => [[
            'id' => 'devices.status',
            'field' => 'devices.status',
            'type' => 'integer',
            'operator' => 'equal',
            'value' => '0',
        ]],
        'valid' => true,
    ];

    private const string BUILDER_SQL = 'SELECT * FROM devices WHERE (devices.device_id = ?) AND devices.status = 0';

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

    private function actingAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    // ── Read ─────────────────────────────────────────────────

    public function testShowReturnsApiAttributes(): void
    {
        $operation = AlertOperation::create(['name' => 'Page on-call']);
        $rule = AlertRule::factory()->create([
            'name' => 'Port Down',
            'severity' => 'critical',
            'disabled' => 1,
            'builder' => self::BUILDER,
            'query' => self::BUILDER_SQL,
            'proc' => 'https://example.org/runbook',
            'notes' => 'Check the port',
            'invert_map' => 1,
            'notify_per_entity' => 1,
            'max_entities' => 5,
            'alert_operation_id' => $operation->id,
            'extra' => ['invert' => true, 'mute' => true, 'recovery' => false, 'options' => ['override_query' => false]],
        ]);
        Sanctum::actingAs($this->userWithPermissions([], 'global-read'));

        $this->getJson(self::URI . '/' . $rule->id)
            ->assertOk()
            ->assertJsonPath('data.type', 'alert-rules')
            ->assertJsonPath('data.attributes', [
                'name' => 'Port Down',
                'severity' => 'critical',
                'isEnabled' => false,
                'builder' => self::BUILDER,
                'query' => self::BUILDER_SQL,
                'overridesQuery' => false,
                'isConditionInverted' => true,
                'isMuted' => true,
                'sendsRecoveryAlerts' => false,
                'sendsAcknowledgementAlerts' => true,
                'notifiesPerEntity' => true,
                'maximumEntities' => 5,
                'procedure' => 'https://example.org/runbook',
                'notes' => 'Check the port',
                'isScopeInverted' => true,
                'alertOperationId' => $operation->id,
            ]);
    }

    public function testListSearchMatchAndSort(): void
    {
        AlertRule::factory()->create(['name' => 'Device Down', 'severity' => 'critical']);
        AlertRule::factory()->create(['name' => 'Port Errors', 'severity' => 'warning']);
        AlertRule::factory()->create(['name' => 'Port Down', 'severity' => 'warning']);
        $this->actingAsAdmin();

        $this->getJson(self::URI)->assertOk()->assertJsonPath('meta.total', 3);
        $this->getJson(self::URI . '?search=Device')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson(self::URI . '?severity=warning&sort=-name')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.attributes.name', 'Port Errors')
            ->assertJsonPath('data.1.attributes.name', 'Port Down');
    }

    public function testViewPermissionOnlyListsRulesForAccessibleDevices(): void
    {
        $accessible = Device::factory()->create();
        $other = Device::factory()->create();
        $visible = AlertRule::factory()->create(['name' => 'Visible']);
        $visible->devices()->attach($accessible->device_id);
        $hidden = AlertRule::factory()->create(['name' => 'Hidden']);
        $hidden->devices()->attach($other->device_id);

        $user = $this->userWithPermissions(['alert-rule.view'], 'user');
        $user->devicesOwned()->attach($accessible->device_id);
        Sanctum::actingAs($user);

        $this->getJson(self::URI)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.attributes.name', 'Visible');
        $this->getJson(self::URI . '/' . $visible->id)->assertOk();
        $this->getJson(self::URI . '/' . $hidden->id)->assertNotFound();
    }

    public function testUserWithoutAlertRulePermissionIsForbidden(): void
    {
        AlertRule::factory()->create();
        Sanctum::actingAs($this->userWithPermissions([]));

        $this->getJson(self::URI)->assertForbidden();
    }

    // ── Create ───────────────────────────────────────────────

    public function testCreateGeneratesQueryFromBuilder(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::URI, [
            'name' => 'Device <b>Unreachable</b>',
            'severity' => 'critical',
            'builder' => self::BUILDER,
            'query' => 'SELECT ignored',
            'isMuted' => true,
            'sendsRecoveryAlerts' => false,
            'procedure' => 'Page on-call',
            'notes' => '<script>x</script>Triggered when down',
        ])->assertCreated()
            ->assertJsonPath('data.attributes.name', 'Device Unreachable')
            ->assertJsonPath('data.attributes.query', self::BUILDER_SQL)
            ->assertJsonPath('data.attributes.isEnabled', true)
            ->assertJsonPath('data.attributes.isMuted', true)
            ->assertJsonPath('data.attributes.sendsRecoveryAlerts', false)
            ->assertJsonPath('data.attributes.sendsAcknowledgementAlerts', true);

        $rule = AlertRule::where('name', 'Device Unreachable')->firstOrFail();
        $this->assertSame(self::BUILDER_SQL, $rule->query);
        $this->assertSame(self::BUILDER, $rule->builder);
        $this->assertEquals(['mute' => true, 'recovery' => false, 'invert' => false, 'acknowledgement' => true], $rule->extra);
        $this->assertSame('Page on-call', $rule->proc);
        $this->assertSame('xTriggered when down', $rule->notes);
        $this->assertSame(0, (int) $rule->disabled);
    }

    public function testCreateWithOverriddenQuery(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::URI, [
            'name' => 'Custom SQL',
            'severity' => 'warning',
            'overridesQuery' => true,
            'query' => 'SELECT * FROM devices WHERE devices.device_id = ?',
            'builder' => [],
        ])->assertCreated()
            ->assertJsonPath('data.attributes.overridesQuery', true)
            ->assertJsonPath('data.attributes.builder', []);

        $rule = AlertRule::where('name', 'Custom SQL')->firstOrFail();
        $this->assertSame('SELECT * FROM devices WHERE devices.device_id = ?', $rule->query);
        $this->assertEquals(['options' => ['override_query' => true], 'invert' => false, 'recovery' => true, 'acknowledgement' => true], $rule->extra);
    }

    public function testCreateRejectsMissingAndInvalidAttributes(): void
    {
        AlertRule::factory()->create(['name' => 'Taken']);
        $this->actingAsAdmin();

        $response = $this->postJson(self::URI, [
            'name' => 'Taken',
            'severity' => 'catastrophic',
            'builder' => ['condition' => 'AND', 'rules' => [['field' => 'devices.status', 'operator' => 'bogus', 'value' => 1]]],
            'isMuted' => 'yes',
            'procedure' => str_repeat('a', 81),
            'maximumEntities' => 0,
            'alertOperationId' => 999999,
        ])->assertUnprocessable();

        $this->assertEqualsCanonicalizing(
            ['/name', '/severity', '/builder', '/isMuted', '/procedure', '/maximumEntities', '/alertOperationId'],
            array_unique(array_column(array_column($response->json('errors'), 'source'), 'pointer')),
        );
    }

    public function testCreateUsesConfiguredDefaults(): void
    {
        LibrenmsConfig::set('alert_rule.invert_rule_match', true);
        LibrenmsConfig::set('alert_rule.recovery_alerts', false);
        LibrenmsConfig::set('alert_rule.acknowledgement_alerts', false);
        $this->actingAsAdmin();

        $this->postJson(self::URI, ['name' => 'Defaults', 'severity' => 'ok', 'builder' => self::BUILDER, 'sendsRecoveryAlerts' => true])
            ->assertCreated()
            ->assertJsonPath('data.attributes.isConditionInverted', true)
            ->assertJsonPath('data.attributes.sendsRecoveryAlerts', true)
            ->assertJsonPath('data.attributes.sendsAcknowledgementAlerts', false);
    }

    public function testOverrideAcceptsNumericBoolean(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::URI, ['name' => 'Numeric', 'severity' => 'ok', 'overridesQuery' => '1', 'query' => 'SELECT * FROM devices WHERE devices.device_id = ?'])
            ->assertCreated()
            ->assertJsonPath('data.attributes.overridesQuery', true);
    }

    public function testCreateRejectsNameThatIsOnlyMarkup(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::URI, ['name' => '<b></b>', 'severity' => 'ok', 'builder' => self::BUILDER])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.source.pointer', '/name');
    }

    public function testCreateRejectsBuilderThatCannotBeConverted(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::URI, [
            'name' => 'Bad Value',
            'severity' => 'ok',
            'builder' => ['condition' => 'AND', 'rules' => [['field' => 'devices.status', 'operator' => 'equal', 'value' => [1, 2]]]],
        ])->assertUnprocessable()
            ->assertJsonPath('errors.0.source.pointer', '/builder');
    }

    public function testCreateRequiresBuilderOrOverriddenQuery(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::URI, ['name' => 'No Builder', 'severity' => 'ok'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.source.pointer', '/builder');

        $this->postJson(self::URI, ['name' => 'No Query', 'severity' => 'ok', 'overridesQuery' => true])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.source.pointer', '/query');

        $this->assertDatabaseMissing('alert_rules', ['name' => 'No Builder']);
        $this->assertDatabaseMissing('alert_rules', ['name' => 'No Query']);
    }

    public function testScopeInversionIsReadOnly(): void
    {
        $this->actingAsAdmin();

        $this->postJson(self::URI, ['name' => 'Inverted', 'severity' => 'ok', 'builder' => self::BUILDER, 'isScopeInverted' => true])
            ->assertCreated()
            ->assertJsonPath('data.attributes.isScopeInverted', false);
    }

    public function testCreatePermissionIsRequired(): void
    {
        Sanctum::actingAs($this->userWithPermissions([], 'global-read'));

        $this->postJson(self::URI, ['name' => 'Rejected', 'severity' => 'ok', 'builder' => self::BUILDER])->assertForbidden();
        $this->assertDatabaseMissing('alert_rules', ['name' => 'Rejected']);
    }

    // ── Update ───────────────────────────────────────────────

    public function testPatchOnlyChangesGivenAttributes(): void
    {
        $rule = AlertRule::factory()->create([
            'name' => 'Patched',
            'severity' => 'warning',
            'builder' => self::BUILDER,
            'query' => self::BUILDER_SQL,
            'notes' => 'keep me',
            'extra' => ['invert' => true, 'count' => -1, 'options' => ['override_query' => false]],
        ]);
        Sanctum::actingAs($this->userWithPermissions(['alert-rule.viewAll', 'alert-rule.update']));

        $this->patchJson(self::URI . '/' . $rule->id, ['severity' => 'critical', 'isMuted' => true, 'isEnabled' => false])
            ->assertOk()
            ->assertJsonPath('data.attributes.severity', 'critical')
            ->assertJsonPath('data.attributes.isEnabled', false)
            ->assertJsonPath('data.attributes.isConditionInverted', true);

        $rule->refresh();
        $this->assertSame('Patched', $rule->name);
        $this->assertSame('keep me', $rule->notes);
        $this->assertSame(1, (int) $rule->disabled);
        $this->assertEquals(['invert' => true, 'count' => -1, 'options' => ['override_query' => false], 'mute' => true], $rule->extra);
    }

    public function testPutKeepsStoredOverride(): void
    {
        $rule = AlertRule::factory()->create([
            'name' => 'Overridden',
            'builder' => [],
            'query' => 'SELECT custom',
            'extra' => ['options' => ['override_query' => true]],
        ]);
        $this->actingAsAdmin();

        $this->putJson(self::URI . '/' . $rule->id, ['name' => 'Overridden', 'severity' => 'ok', 'query' => 'SELECT changed'])
            ->assertOk()
            ->assertJsonPath('data.attributes.query', 'SELECT changed');
    }

    public function testUpdateRegeneratesQueryWhenOverrideIsTurnedOff(): void
    {
        $rule = AlertRule::factory()->create([
            'builder' => self::BUILDER,
            'query' => 'SELECT custom',
            'extra' => ['options' => ['override_query' => true]],
        ]);
        $this->actingAsAdmin();

        $this->patchJson(self::URI . '/' . $rule->id, ['overridesQuery' => false])
            ->assertOk()
            ->assertJsonPath('data.attributes.query', self::BUILDER_SQL);
    }

    public function testPatchDoesNotRevalidateStoredBuilder(): void
    {
        $rule = AlertRule::factory()->create([
            'builder' => ['condition' => 'AND', 'rules' => []],
            'query' => 'SELECT legacy',
            'extra' => ['options' => ['override_query' => false]],
        ]);
        $this->actingAsAdmin();

        $this->patchJson(self::URI . '/' . $rule->id, ['isEnabled' => false])->assertOk();
        $this->assertSame('SELECT legacy', $rule->fresh()->query);
    }

    public function testQueryIsOnlyWritableWhenOverridden(): void
    {
        $rule = AlertRule::factory()->create([
            'builder' => self::BUILDER,
            'query' => self::BUILDER_SQL,
            'extra' => ['options' => ['override_query' => false]],
        ]);
        $this->actingAsAdmin();

        $this->patchJson(self::URI . '/' . $rule->id, ['query' => 'SELECT * FROM devices WHERE 1=1'])
            ->assertOk()
            ->assertJsonPath('data.attributes.query', self::BUILDER_SQL);

        $rule->update(['extra' => ['options' => ['override_query' => true]]]);
        $this->patchJson(self::URI . '/' . $rule->id, ['query' => null])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.source.pointer', '/query');
        $this->assertSame(self::BUILDER_SQL, $rule->fresh()->query);
    }

    public function testDisablingClearsOpenAlerts(): void
    {
        $rule = AlertRule::factory()->create(['disabled' => 0]);
        $device = Device::factory()->create();
        $alert = Alert::factory()->create(['rule_id' => $rule->id, 'device_id' => $device->device_id, 'state' => AlertState::ACTIVE, 'open' => 1]);
        $this->actingAsAdmin();

        $this->patchJson(self::URI . '/' . $rule->id, ['isEnabled' => false])->assertOk();

        $alert->refresh();
        $this->assertSame(AlertState::CLEAR, (int) $alert->state);
        $this->assertSame(0, (int) $alert->open);
    }

    public function testUpdatePermissionAppliesToEveryRule(): void
    {
        $rule = AlertRule::factory()->create(['severity' => 'ok']);
        Sanctum::actingAs($this->userWithPermissions(['alert-rule.update']));

        $this->patchJson(self::URI . '/' . $rule->id, ['severity' => 'critical'])->assertOk();
        $this->assertSame('critical', $rule->fresh()->severity);
    }

    public function testUpdateKeepsNameUniqueAndAllowsItsOwnName(): void
    {
        AlertRule::factory()->create(['name' => 'Other Rule']);
        $rule = AlertRule::factory()->create(['name' => 'This Rule', 'builder' => self::BUILDER]);
        $this->actingAsAdmin();

        $this->putJson(self::URI . '/' . $rule->id, ['name' => 'This Rule', 'severity' => 'ok', 'builder' => self::BUILDER])->assertOk();
        $this->patchJson(self::URI . '/' . $rule->id, ['name' => 'Other <i>Rule</i>'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.0.source.pointer', '/name');
    }

    public function testUpdatePermissionIsRequired(): void
    {
        $rule = AlertRule::factory()->create(['severity' => 'ok']);
        Sanctum::actingAs($this->userWithPermissions(['alert-rule.viewAll']));

        $this->patchJson(self::URI . '/' . $rule->id, ['severity' => 'critical'])->assertForbidden();
        $this->assertSame('ok', $rule->fresh()->severity);
    }

    // ── Delete ───────────────────────────────────────────────

    public function testDeleteRemovesRelatedRecords(): void
    {
        $rule = AlertRule::factory()->create();
        $device = Device::factory()->create();
        $rule->devices()->attach($device->device_id);
        Alert::factory()->create(['rule_id' => $rule->id, 'device_id' => $device->device_id]);
        AlertLog::factory()->create(['rule_id' => $rule->id, 'device_id' => $device->device_id]);
        Sanctum::actingAs($this->userWithPermissions(['alert-rule.viewAll', 'alert-rule.delete']));

        $this->deleteJson(self::URI . '/' . $rule->id)->assertNoContent();

        $this->assertDatabaseMissing('alert_rules', ['id' => $rule->id]);
        $this->assertDatabaseMissing('alerts', ['rule_id' => $rule->id]);
        $this->assertDatabaseMissing('alert_log', ['rule_id' => $rule->id]);
        $this->assertDatabaseMissing('alert_device_map', ['rule_id' => $rule->id]);
    }

    public function testDeletePermissionIsRequired(): void
    {
        $rule = AlertRule::factory()->create();
        Sanctum::actingAs($this->userWithPermissions(['alert-rule.viewAll', 'alert-rule.update']));

        $this->deleteJson(self::URI . '/' . $rule->id)->assertForbidden();
        $this->assertDatabaseHas('alert_rules', ['id' => $rule->id]);
    }
}
