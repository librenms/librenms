<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Support\Carbon;
use LibreNMS\Alert\AlertRules;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\Severity;
use LibreNMS\Tests\TestCase;

class AlertRulesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->dbSetUp();
    }

    protected function tearDown(): void
    {
        $this->dbTearDown();
        parent::tearDown();
    }

    public function testRunRulesSkipsUnderMaintenance(): void
    {
        $device = Device::factory()->create();

        $schedule = \App\Models\AlertSchedule::forceCreate([
            'title' => 'Maintenance',
            'notes' => '',
            'start' => \Carbon\Carbon::now()->subHour(),
            'end' => \Carbon\Carbon::now()->addHour(),
            'recurring' => 0,
            'behavior' => 1, // SkipAlerts
        ]);
        $schedule->devices()->attach($device->device_id);

        $alertRules = new AlertRules($device);
        $result = $alertRules->run();

        $this->assertFalse($result);
    }

    public function testRunRulesDisableAlerting(): void
    {
        $device = Device::factory()->create(['disable_notify' => 1]);
        $rule = AlertRule::factory()->create();

        Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 0,
            'info' => [],
        ]);

        $alertRules = new AlertRules($device);
        $result = $alertRules->run();

        $this->assertFalse($result);
        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::CLEAR,
            'open' => 0,
        ]);
    }

    public function testRunRulesTriggersAlert(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
        ]);

        $this->assertDatabaseHas('alert_log', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);
    }

    public function testRunRulesUpdatesExistingAlert(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        // Pre-existing recovered alert
        Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::RECOVERED,
            'open' => 1,
            'alerted' => 0,
            'info' => [],
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);
    }

    public function testRunRulesNoChangeOnActiveAlert(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 0,
            'info' => [],
        ]);

        $log = AlertLog::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'details' => ['old' => 'data'],
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        // State should still be ACTIVE
        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);

        // AlertLog should be updated with new details (contacts and rule)
        $updatedLog = AlertLog::find($log->id);
        $this->assertArrayHasKey('contacts', $updatedLog->details);
        $this->assertArrayHasKey('rule', $updatedLog->details);
    }

    public function testRunRulesSkipsAcknowledgedAlert(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACKNOWLEDGED,
            'open' => 1,
            'alerted' => 0,
            'info' => [],
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACKNOWLEDGED,
        ]);

        // No new AlertLog should be created for ACTIVE
        $this->assertEquals(0, AlertLog::where('state', AlertState::ACTIVE)->count());
    }

    public function testRunRulesClearsAlert(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 0,
            'info' => [],
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::RECOVERED,
        ]);

        $this->assertDatabaseHas('alert_log', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::RECOVERED,
        ]);
    }

    public function testRunRulesInvertsResult(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 1',
            'extra' => ['invert' => true],
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        // status=1 matches query, but invert=true, so doalert=false
        $this->assertDatabaseMissing('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);
    }

    public function testRunRulesHandlesQueryError(): void
    {
        $device = Device::factory()->create();
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT invalid_column FROM devices WHERE device_id = ?',
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('eventlog', [
            'device_id' => $device->device_id,
            'type' => 'alert',
            'severity' => Severity::Error,
        ]);
    }

    public function testRulesFiltering(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $otherDevice = Device::factory()->create(['status' => 0]);

        // 1. Rule mapped to this device - should trigger
        $ruleMapped = AlertRule::factory()->create([
            'name' => 'Mapped Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);
        $ruleMapped->devices()->attach($device->device_id);

        // 2. Rule mapped to OTHER device - should NOT trigger
        $ruleOtherMapped = AlertRule::factory()->create([
            'name' => 'Other Mapped Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);
        $ruleOtherMapped->devices()->attach($otherDevice->device_id);

        // 3. Global rule - should trigger
        $ruleGlobal = AlertRule::factory()->create([
            'name' => 'Global Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $ruleMapped->id,
            'state' => AlertState::ACTIVE,
        ]);

        $this->assertDatabaseMissing('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $ruleOtherMapped->id,
        ]);

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $ruleGlobal->id,
            'state' => AlertState::ACTIVE,
        ]);
    }

    public function testRulesFilteringInverted(): void
    {
        $device = Device::factory()->create(['status' => 0]);

        // 1. Rule mapped to this device, BUT inverted - should NOT trigger
        $ruleInvertedMapped = AlertRule::factory()->create([
            'name' => 'Inverted Mapped Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
            'invert_map' => 1,
        ]);
        $ruleInvertedMapped->devices()->attach($device->device_id);

        // 2. Rule mapped to OTHER device, inverted - should trigger
        $otherDevice = Device::factory()->create();
        $ruleInvertedOtherMapped = AlertRule::factory()->create([
            'name' => 'Inverted Other Mapped Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
            'invert_map' => 1,
        ]);
        $ruleInvertedOtherMapped->devices()->attach($otherDevice->device_id);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseMissing('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $ruleInvertedMapped->id,
        ]);

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $ruleInvertedOtherMapped->id,
            'state' => AlertState::ACTIVE,
        ]);
    }

    public function testRulesFilteringGroups(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $group = \App\Models\DeviceGroup::factory()->create();
        $group->devices()->attach($device->device_id);

        $rule = AlertRule::factory()->create([
            'name' => 'Group Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);
        $rule->groups()->attach($group->id);

        $otherGroup = \App\Models\DeviceGroup::factory()->create();
        $otherRule = AlertRule::factory()->create([
            'name' => 'Other Group Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);
        $otherRule->groups()->attach($otherGroup->id);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);

        $this->assertDatabaseMissing('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $otherRule->id,
        ]);
    }

    public function testRulesFilteringLocations(): void
    {
        $location = \App\Models\Location::factory()->create();
        $device = Device::factory()->create(['status' => 0, 'location_id' => $location->id]);

        $rule = AlertRule::factory()->create([
            'name' => 'Location Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);
        $rule->locations()->attach($location->id);

        $otherLocation = \App\Models\Location::factory()->create();
        $otherRule = AlertRule::factory()->create([
            'name' => 'Other Location Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);
        $otherRule->locations()->attach($otherLocation->id);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);

        $this->assertDatabaseMissing('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $otherRule->id,
        ]);
    }

    public function testRunRulesHandlesWorseStateAsNoChange(): void
    {
        $this->runActiveStateTest(AlertState::WORSE);
    }

    public function testRunRulesHandlesBetterStateAsNoChange(): void
    {
        $this->runActiveStateTest(AlertState::BETTER);
    }

    public function testRunRulesHandlesChangedStateAsNoChange(): void
    {
        $this->runActiveStateTest(AlertState::CHANGED);
    }

    private function runActiveStateTest(int $state): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        $initialTimestamp = \Carbon\Carbon::now()->subHour();

        // Pre-existing alert in given state
        $alert = Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => $state,
            'open' => 1,
            'alerted' => 1,
            'info' => [],
            'timestamp' => $initialTimestamp,
        ]);

        AlertLog::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => $state,
            'details' => ['old' => 'data'],
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        // State should still be the same, not reset to ACTIVE
        $alert->refresh();
        $this->assertEquals($state, $alert->state, "Alert state was reset from $state to ACTIVE");

        // Timestamp should NOT have changed
        $actual = $alert->timestamp instanceof Carbon ? $alert->timestamp->toDateTimeString() : $alert->timestamp;
        $this->assertEquals($initialTimestamp->toDateTimeString(), $actual, 'Alert timestamp was reset');

        // AlertLog should have been updated but state remains same
        $updatedLog = AlertLog::where('device_id', $device->device_id)
            ->where('rule_id', $rule->id)
            ->latest('id')
            ->first();
        $this->assertEquals($state, $updatedLog->state->value, "Latest AlertLog state was changed from $state");
        $this->assertArrayHasKey('contacts', $updatedLog->details);
    }

    public function testRunRulesWithDeviceId(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        $alertRules = new AlertRules($device->device_id);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);
    }

    public function testRunRulesSkipsDisabledRule(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
            'disabled' => 1,
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseMissing('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
        ]);
    }

    public function testRunRulesUsesQueryBuilderJson(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $builderJson = [
            'condition' => 'AND',
            'rules' => [
                [
                    'field' => 'devices.status',
                    'operator' => 'equal',
                    'value' => '0',
                ],
            ],
        ];
        $rule = AlertRule::factory()->create([
            'query' => '',
            'builder' => $builderJson,
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);
    }

    public function testRunRulesSkipsEmptySql(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => '',
            'builder' => [],
        ]);

        $alertRules = new AlertRules($device);
        $result = $alertRules->run();

        $this->assertTrue($result);
        $this->assertDatabaseMissing('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
        ]);
    }

    public function testRunRulesConvertsBinaryIp(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        // MySQL INET6_ATON converts string to binary
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT INET6_ATON("192.0.2.1") AS ip FROM devices WHERE device_id = ?',
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $updatedLog = AlertLog::where('device_id', $device->device_id)
            ->where('rule_id', $rule->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($updatedLog);
        $details = $updatedLog->details;
        $this->assertEquals('192.0.2.1', $details['rule'][0]['ip']);
    }

    public function testRunRulesRecoveryWithoutPreExistingAlert(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::RECOVERED,
        ]);
    }

    public function testRunRulesNoChangeOnActiveAlertWithoutLog(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
        ]);

        Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 0,
            'info' => [],
        ]);

        // Note: No AlertLog is created in DB for this rule.

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);
        // AlertLog was not created beforehand, and shouldn't cause errors.
    }

    public function testRunRulesInvertsResultToTriggerAlert(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        // The query returns empty for status = 0.
        // With invert = true, empty means trigger alert (do_alert = true).
        $rule = AlertRule::factory()->create([
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
            'extra' => ['invert' => true],
        ]);

        $alertRules = new AlertRules($device);
        $alertRules->run();

        $this->assertDatabaseHas('alerts', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
        ]);
    }
}
