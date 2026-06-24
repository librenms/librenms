<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\Device;
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
}
