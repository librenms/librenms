<?php
/**
 * AlertNotificationsTest.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Feature;

use App\Models\Alert;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Support\Facades\DB;
use LibreNMS\Alert\AlertNotifications;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\TestCase;

class AlertNotificationsTest extends TestCase
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

    public function testClearStaleAlerts(): void
    {
        // Alert with non-existent device
        $rule = AlertRule::factory()->create();
        $alertId = DB::table('alerts')->insertGetId([
            'device_id' => 99999,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 0,
            'info' => json_encode([]),
        ]);

        $alerts = new AlertNotifications();
        $alerts->clearStaleAlerts();

        $this->assertDatabaseMissing('alerts', ['id' => $alertId]);
    }

    public function testRunAlertsSendsNotification(): void
    {
        $device = Device::factory()->create();

        \App\Models\AlertTemplate::forceCreate([
            'name' => 'Default Alert Template',
            'template' => 'Test Body',
            'title' => 'Test Title',
        ]);

        $operation = \App\Models\AlertOperation::forceCreate([
            'name' => 'Default',
        ]);
        \App\Models\AlertOperationSegment::forceCreate([
            'alert_operation_id' => $operation->id,
            'operation_phase' => 'problem',
            'escalation_step_from' => 1,
            'escalation_step_to' => null,
            'position' => 1,
        ]);

        $rule = AlertRule::factory()->create([
            'name' => 'Test Rule',
            'extra' => ['recovery' => true],
            'alert_operation_id' => $operation->id,
        ]);

        $alert = Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 0,
            'info' => [],
        ]);

        AlertLog::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'details' => ['rule' => [], 'contacts' => []],
        ]);

        $alerts = new AlertNotifications();

        // We need to capture the output or check side effects
        ob_start();
        $alerts->runAlerts();
        $output = ob_get_clean();

        if (strpos($output, 'Muted') !== false) {
            echo "\nDEBUG OUTPUT:\n" . $output . "\n";
            $loaded = $alerts->loadAlerts('alerts.id = ' . $alert->id);
            var_dump($loaded);
        }

        $this->assertStringContainsString('Issuing Alert-UID', $output);

        $alert->refresh();
        $this->assertEquals(AlertState::ACTIVE, $alert->alerted);
    }

    public function testRunAlertsRecoveryNotification(): void
    {
        $device = Device::factory()->create();

        \App\Models\AlertTemplate::forceCreate([
            'name' => 'Default Alert Template',
            'template' => 'Test Body',
            'title' => 'Test Title',
            'title_rec' => 'Recovery Title',
        ]);

        $operation = \App\Models\AlertOperation::forceCreate([
            'name' => 'Default',
        ]);
        \App\Models\AlertOperationSegment::forceCreate([
            'alert_operation_id' => $operation->id,
            'operation_phase' => 'problem',
            'escalation_step_from' => 1,
            'escalation_step_to' => null,
            'position' => 1,
        ]);

        $rule = AlertRule::factory()->create([
            'name' => 'Test Rule',
            'extra' => ['recovery' => true],
            'alert_operation_id' => $operation->id,
        ]);

        $alert = Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::RECOVERED,
            'open' => 1,
            'alerted' => 1,
            'info' => [],
        ]);

        AlertLog::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'details' => ['rule' => [], 'contacts' => []],
        ]);

        AlertLog::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::RECOVERED,
            'details' => [],
        ]);

        $alerts = new AlertNotifications();

        ob_start();
        $alerts->runAlerts();
        $output = ob_get_clean();

        $this->assertStringContainsString('Issuing Alert-UID', $output);
        $this->assertStringContainsString('/0:', $output);

        $alert->refresh();
        $this->assertEquals(0, $alert->open);
        $this->assertEquals(0, $alert->alerted);
    }

    public function testRunAcksSendsNotification(): void
    {
        $device = Device::factory()->create();

        \App\Models\AlertTemplate::forceCreate([
            'name' => 'Default Alert Template',
            'template' => 'Test Body',
            'title' => 'Test Title',
        ]);

        $operation = \App\Models\AlertOperation::forceCreate([
            'name' => 'Default',
        ]);
        \App\Models\AlertOperationSegment::forceCreate([
            'alert_operation_id' => $operation->id,
            'operation_phase' => 'problem',
            'escalation_step_from' => 1,
            'escalation_step_to' => null,
            'position' => 1,
        ]);

        $rule = AlertRule::factory()->create([
            'name' => 'Test Rule',
            'extra' => ['acknowledgement' => true],
            'alert_operation_id' => $operation->id,
        ]);

        $alert = Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACKNOWLEDGED,
            'open' => 1,
            'alerted' => 1,
            'info' => [],
        ]);

        AlertLog::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACKNOWLEDGED,
            'details' => ['contacts' => []],
        ]);

        $alerts = new AlertNotifications();

        ob_start();
        $alerts->runAcks();
        $output = ob_get_clean();

        $this->assertStringContainsString('Issuing Alert-UID', $output);
        $this->assertStringContainsString('/2:', $output);

        $alert->refresh();
        $this->assertEquals(0, $alert->open);
    }

    public function testRunFollowUpSendsNotification(): void
    {
        $device = Device::factory()->create(['status' => 0]);

        $operation = \App\Models\AlertOperation::forceCreate([
            'name' => 'Default',
        ]);
        \App\Models\AlertOperationSegment::forceCreate([
            'alert_operation_id' => $operation->id,
            'operation_phase' => 'problem',
            'escalation_step_from' => 1,
            'escalation_step_to' => null,
            'position' => 1,
        ]);

        $rule = AlertRule::factory()->create([
            'name' => 'Test Rule',
            'query' => 'SELECT * FROM devices WHERE device_id = ? AND status = 0',
            'alert_operation_id' => $operation->id,
        ]);

        // ACTIVE alert, but already processed (open=0)
        $alert = Alert::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'open' => 0,
            'alerted' => 1,
            'info' => [],
        ]);

        // Previous log with 0 faults (so it will get worse)
        AlertLog::create([
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::ACTIVE,
            'details' => ['rule' => [], 'contacts' => []],
        ]);

        $alerts = new AlertNotifications();

        ob_start();
        $alerts->runFollowUp();
        $output = ob_get_clean();

        $this->assertStringContainsString('Alert #', $output);
        $this->assertStringContainsString('Worse', $output);

        $alert->refresh();
        $this->assertEquals(1, $alert->open);
        $this->assertEquals(AlertState::WORSE, $alert->state);

        $this->assertDatabaseHas('alert_log', [
            'device_id' => $device->device_id,
            'rule_id' => $rule->id,
            'state' => AlertState::WORSE,
        ]);
    }
}
