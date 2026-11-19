<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\Alert;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Alerting\AlertRuleDefinitions;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AlertRulesPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
        AlertRule::query()->delete(); // the test database may hold the default rules
    }

    public function testIndexListsRules(): void
    {
        $rule = AlertRule::factory()->create(['name' => 'Rule <b>', 'severity' => 'warning']);
        $rule->devices()->attach(Device::factory()->create(['hostname' => 'mapped.example.com']));

        $this->actingAs($this->admin())
            ->get(route('alert-rules.index'))
            ->assertOk()
            ->assertSee('Rule &lt;b&gt;', false)
            ->assertSee('mapped.example.com')
            ->assertSee('Warning')
            ->assertSee(route('alert-rule.create'))
            ->assertSee(route('alert-rule.edit', $rule))
            ->assertDontSee('data-alert-rule-editor', false)
            ->assertDontSee('create the default alert rules');
    }

    public function testDevicePageOnlyListsRulesForTheDevice(): void
    {
        $device = Device::factory()->create();
        AlertRule::factory()->create(['name' => 'Global rule']);
        AlertRule::factory()->create(['name' => 'Other device rule'])->devices()->attach(Device::factory()->create());
        AlertRule::factory()->create(['name' => 'This device rule'])->devices()->attach($device);

        $response = $this->actingAs($this->admin())
            ->get(route('device.edit.alert-rules', $device))
            ->assertOk()
            ->assertSee(route('alert-rule.create', ['device' => $device->device_id]));

        $response->assertSee('Global rule')->assertSee('This device rule')->assertDontSee('Other device rule');
    }

    public function testListingAndCopyPickerFollowRuleVisibilityWithoutRequiringAlerts(): void
    {
        $user = User::factory()->create(['enabled' => 1])->assignRole('user');
        $user->givePermissionTo(Permission::findOrCreate('alert-rule.create'));
        $location = Location::factory()->create();
        $device = Device::factory()->create(['location_id' => $location->id]);
        $user->devicesOwned()->attach($device);
        $group = DeviceGroup::factory()->create();
        $group->devices()->attach($device);

        $deviceRule = AlertRule::factory()->create(['name' => 'Mapped via device']);
        $deviceRule->devices()->attach($device);
        $groupRule = AlertRule::factory()->create(['name' => 'Mapped via group']);
        $groupRule->groups()->attach($group);
        $locationRule = AlertRule::factory()->create(['name' => 'Mapped via location']);
        $locationRule->locations()->attach($location);
        $unmapped = AlertRule::factory()->create(['name' => 'Unmapped rule']);
        AlertRule::factory()->create(['name' => 'Inaccessible rule'])->devices()->attach(Device::factory()->create());
        Alert::factory()->create(['rule_id' => $unmapped->id, 'device_id' => $device->device_id]);

        $this->actingAs($user)->get(route('alert-rules.index'))
            ->assertOk()
            ->assertViewHas('rules', fn ($rules) => $rules->pluck('id')->all() === [$deviceRule->id, $groupRule->id, $locationRule->id])
            ->assertSee('Mapped via device')
            ->assertSee('Mapped via group')
            ->assertSee('Mapped via location')
            ->assertDontSee('Unmapped rule')
            ->assertDontSee('Inaccessible rule');
        foreach ([$deviceRule, $groupRule, $locationRule] as $rule) {
            $this->getJson(route('alert-rule.show', $rule))->assertOk();
        }
        $this->getJson(route('alert-rule.show', $unmapped))->assertForbidden();
        $this->getJson(route('alert-rule-template.rule', $unmapped))->assertForbidden();
        $this->get(route('alert-rule.create', ['copy' => $unmapped->id]))->assertForbidden();
        $this->get(route('alert-rule.create', ['copy' => $deviceRule->id]))
            ->assertOk()
            ->assertSee('Mapped via device - Copy')
            ->assertViewHas('copyableRules', fn ($rules) => $rules->pluck('id')->all() === [$deviceRule->id, $groupRule->id, $locationRule->id]);

        $user->givePermissionTo(Permission::findOrCreate('alert-rule.viewAll'));
        $this->get(route('alert-rules.index'))->assertOk()->assertSee('Unmapped rule')->assertSee('Inaccessible rule');
    }

    public function testUserCanEditRulesWithoutCreationPermission(): void
    {
        $user = User::factory()->create(['enabled' => 1])->assignRole('user');
        $user->givePermissionTo(Permission::findOrCreate('alert-rule.update'));
        $device = Device::factory()->create();
        $user->devicesOwned()->attach($device);
        $rule = AlertRule::factory()->create();
        $rule->devices()->attach($device);

        $this->actingAs($user)->get(route('alert-rules.index'))
            ->assertOk()
            ->assertSee('Edit alert rule')
            ->assertSee(route('alert-rule.edit', $rule))
            ->assertDontSee('Create new alert rule')
            ->assertDontSee('data-alert-rule-editor', false);
        $this->get(route('alert-rule.edit', $rule))
            ->assertOk()
            ->assertSee('data-alert-rule-editor', false)
            ->assertDontSee('id="search_rule_modal"', false);
        $this->get(route('alert-rule.create'))->assertForbidden();
    }

    public function testFullPageEditorRetainsDeviceContextAndReturnsToItsListing(): void
    {
        $device = Device::factory()->create();
        $context = ['device' => $device->device_id];
        $backUrl = route('device.edit.alert-rules', $device);
        $createUrl = route('alert-rule.create', $context);

        $this->actingAs($this->admin())->get($createUrl)
            ->assertOk()
            ->assertSee($backUrl)
            ->assertViewHas('maps', [['id' => $device->device_id, 'text' => $device->display]]);

        $payload = $context + [
            'name' => 'Device rule', 'severity' => 'warning', 'override_query' => '1',
            'adv_query' => 'SELECT * FROM devices WHERE status = 1', 'maps' => [(string) $device->device_id],
        ];
        $this->post(route('alert-rule.store'), $payload)->assertRedirect($backUrl);
        $rule = AlertRule::where('name', $payload['name'])->firstOrFail();
        $editUrl = route('alert-rule.edit', ['alert_rule' => $rule] + $context);

        $this->get($editUrl)->assertOk()->assertSee('name="_method" value="PUT"', false)->assertSee($backUrl);
        $payload['name'] = 'Updated device rule';
        $payload['adv_query'] = '';
        $this->from($editUrl)->put(route('alert-rule.update', $rule), $payload)
            ->assertRedirect($editUrl)->assertSessionHasErrors('adv_query');
        $this->get($editUrl)->assertOk()->assertSee('value="Updated device rule"', false);

        $payload['adv_query'] = $rule->query;
        $this->put(route('alert-rule.update', $rule), $payload)->assertRedirect($backUrl);
        $this->assertSame('Updated device rule', $rule->refresh()->name);
    }

    public function testListingShowsChangedAndAcknowledgedAlertsAsAlerting(): void
    {
        foreach ([AlertState::ACTIVE, AlertState::ACKNOWLEDGED, AlertState::WORSE, AlertState::BETTER, AlertState::CHANGED, AlertState::CLEAR] as $state) {
            Alert::factory()->create(['state' => $state]);
        }

        $mixed = AlertRule::factory()->create();
        Alert::factory()->for($mixed, 'rule')->acknowledged()->create();
        Alert::factory()->for($mixed, 'rule')->create(['state' => AlertState::CHANGED]);

        $response = $this->actingAs($this->admin())->get(route('alert-rules.index'))->assertOk();
        $this->assertSame(6, substr_count((string) $response->getContent(), 'Some devices matching this rule are alerting'));
        $this->assertSame(2, substr_count((string) $response->getContent(), 'Some alerts for this rule are acknowledged'));
        $this->assertSame(1, substr_count((string) $response->getContent(), 'All devices matching this rule are OK'));
    }

    public function testListingStatusOnlyUsesAccessibleAlerts(): void
    {
        $user = User::factory()->create(['enabled' => 1])->assignRole('user');
        $accessible = Device::factory()->create();
        $inaccessible = Device::factory()->create();
        $user->devicesOwned()->attach($accessible);
        $rule = AlertRule::factory()->create(['name' => 'Shared rule']);
        $rule->devices()->attach([$accessible->device_id, $inaccessible->device_id]);
        Alert::factory()->for($rule, 'rule')->for($accessible, 'device')->clear()->create();
        Alert::factory()->for($rule, 'rule')->for($inaccessible, 'device')->acknowledged()->create();

        $this->actingAs($user)->get(route('alert-rules.index'))
            ->assertOk()
            ->assertSee('Shared rule')
            ->assertSee('No accessible devices matching this rule are alerting')
            ->assertDontSee('Some devices matching this rule are alerting')
            ->assertDontSee('Some alerts for this rule are acknowledged');
    }

    public function testSqlOverrideCanBeSavedAndCopiedWithoutBuilderRules(): void
    {
        $operation = AlertOperation::create(['name' => 'Test operation']);
        $sql = 'SELECT * FROM devices WHERE devices.status = 1';
        $payload = [
            'name' => 'Custom SQL rule',
            'severity' => 'warning',
            'proc' => 'https://example.com/procedure',
            'notes' => 'Rule notes',
            'notify_per_entity' => true,
            'max_entities' => 4,
            'alert_operation_id' => $operation->id,
        ];

        $this->actingAs($this->admin())->postJson(route('alert-rule.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('builder_json');
        $this->postJson(route('alert-rule.store'), $payload + ['override_query' => '1'])
            ->assertUnprocessable()->assertJsonValidationErrors('adv_query');
        $this->postJson(route('alert-rule.store'), $payload + ['override_query' => '1', 'adv_query' => $sql])
            ->assertOk()->assertJsonPath('status', 'ok');

        $rule = AlertRule::where('name', $payload['name'])->firstOrFail();
        $this->assertSame($sql, $rule->query);
        $this->getJson(route('alert-rule-template.rule', $rule))
            ->assertOk()
            ->assertJsonPath('name', 'Custom SQL rule - Copy')
            ->assertJsonPath('proc', $payload['proc'])
            ->assertJsonPath('notes', $payload['notes'])
            ->assertJsonPath('adv_query', $sql)
            ->assertJsonPath('notify_per_entity', true)
            ->assertJsonPath('max_entities', 4)
            ->assertJsonPath('alert_operation_name', $operation->name)
            ->assertJsonMissingPath('maps');
    }

    public function testCreateDefaultRules(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('alert-rules.index'))
            ->assertSee('create the default alert rules');

        $this->actingAs($admin)
            ->post(route('alert-rules.defaults'))
            ->assertRedirect();

        $this->post(route('alert-rules.defaults'))->assertRedirect();
        $rules = AlertRule::where('name', 'Device rebooted')->get();
        $this->assertCount(1, $rules);
        $this->assertNotEmpty($rules->firstOrFail()->query);
        $this->assertSame(1, $rules->firstOrFail()->extra['count']);
    }

    public function testImportPreservesBundledRuleOptions(): void
    {
        $id = array_search('Device rebooted', array_column(AlertRuleDefinitions::all(), 'name'), true);
        $this->assertIsInt($id);
        $template = $this->actingAs($this->admin())->getJson(route('alert-rule-template', $id))
            ->assertOk()->assertJsonPath('extra.count', 1)->json();

        $this->postJson(route('alert-rule.store'), [
            'name' => 'Imported reboot rule',
            'severity' => $template['severity'],
            'builder_json' => json_encode($template['builder']),
            'template_id' => $template['template_id'],
        ])->assertOk()->assertJsonPath('status', 'ok');

        $this->assertSame(1, AlertRule::where('name', 'Imported reboot rule')->firstOrFail()->extra['count']);
    }

    public function testInvalidBuilderOrMappingLeavesTheRuleAndItsTargetsIntact(): void
    {
        $rule = AlertRule::factory()->create();
        $device = Device::factory()->create();
        $rule->devices()->attach($device);
        $payload = ['name' => 'Updated rule', 'severity' => $rule->severity];

        $this->actingAs($this->admin())->putJson(route('alert-rule.update', $rule), $payload + ['builder_json' => '[]'])
            ->assertUnprocessable()->assertJsonValidationErrors('builder_json');
        $this->putJson(route('alert-rule.update', $rule), $payload + [
            'override_query' => true, 'adv_query' => $rule->query, 'maps' => ['invalid'],
        ])->assertUnprocessable()->assertJsonValidationErrors('maps.0');
        $this->putJson(route('alert-rule.update', $rule), $payload + [
            'override_query' => true, 'adv_query' => $rule->query, 'maps' => ['l2147483647'],
        ])->assertUnprocessable()->assertJsonValidationErrors('maps');

        $this->assertNotSame($payload['name'], $rule->refresh()->name);
        $this->assertSame([$device->device_id], $rule->devices()->pluck('devices.device_id')->all());
    }

    public function testFailedMappingSyncRollsBackEarlierWrites(): void
    {
        $rule = AlertRule::factory()->create();
        $device = Device::factory()->create();
        $replacement = Device::factory()->create();
        $location = Location::factory()->create();
        $rule->devices()->attach($device);
        $rule->getConnection()->beforeExecuting(function (string $query): void {
            if (str_starts_with($query, 'insert') && str_contains($query, 'alert_location_map')) {
                throw new \RuntimeException('Could not insert location mapping');
            }
        });

        $this->actingAs($this->admin())->putJson(route('alert-rule.update', $rule), [
            'name' => 'Updated rule',
            'severity' => $rule->severity,
            'override_query' => true,
            'adv_query' => $rule->query,
            'maps' => [(string) $replacement->device_id, 'l' . $location->id],
        ])->assertInternalServerError();

        $this->assertNotSame('Updated rule', $rule->refresh()->name);
        $this->assertSame([$device->device_id], $rule->devices()->pluck('devices.device_id')->all());
        $this->assertFalse($rule->locations()->exists());
    }

    public function testCreatingDefaultsRequiresPermission(): void
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        $this->actingAs($user)->post(route('alert-rules.defaults'))->assertForbidden();
        $this->assertFalse(AlertRule::exists());
    }

    public function testRuleWritesRequirePermissionBeforeValidation(): void
    {
        $user = User::factory()->create(['enabled' => 1])->assignRole('user');
        $rule = AlertRule::factory()->create();

        $this->actingAs($user)->postJson(route('alert-rule.store'), [])->assertForbidden();

        $user->givePermissionTo(Permission::findOrCreate('alert-rule.create'));
        $this->postJson(route('alert-rule.store'), [])->assertUnprocessable();
        $this->putJson(route('alert-rule.update', $rule), [])->assertForbidden();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }
}
