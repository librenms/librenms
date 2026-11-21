<?php

namespace LibreNMS\Tests\Feature\Alert;

use App\Models\AlertFault;
use App\Models\AlertRule;
use App\Models\Application;
use App\Models\Device;
use LibreNMS\Alert\AlertRules;
use LibreNMS\Enum\AlertState;
use LibreNMS\Tests\InMemoryDbTestCase;

class DeletedApplicationAlertsTest extends InMemoryDbTestCase
{
    public function testRuleFaultRecoversWhenApplicationIsDeleted(): void
    {
        $device = Device::factory()->create();
        $application = Application::factory()->create([
            'device_id' => $device->device_id,
            'app_type' => 'ntp',
            'app_state' => 'UNKNOWN',
        ]);
        $rule = AlertRule::factory()->create([
            'query' => '',
            'builder' => [
                'condition' => 'AND',
                'valid' => true,
                'rules' => [
                    [
                        'id' => 'applications.app_state',
                        'field' => 'applications.app_state',
                        'type' => 'string',
                        'input' => 'text',
                        'operator' => 'not_equal',
                        'value' => 'OK',
                    ],
                ],
            ],
        ]);

        new AlertRules($device)->run();

        $this->assertFaultState($device, $rule, AlertState::ACTIVE);

        // GUI deletion is a soft delete; the rule query must exclude soft-deleted
        // applications, so the fault must not stay alive for a deleted application.
        $application->delete();

        new AlertRules($device)->run();

        $this->assertFaultState($device, $rule, AlertState::RECOVERED);
    }

    public function testRuleFaultRecoversWhenApplicationIsPurged(): void
    {
        $device = Device::factory()->create();
        $application = Application::factory()->create([
            'device_id' => $device->device_id,
            'app_type' => 'ntp',
            'app_state' => 'UNKNOWN',
        ]);
        $rule = AlertRule::factory()->create([
            'query' => '',
            'builder' => [
                'condition' => 'AND',
                'valid' => true,
                'rules' => [
                    [
                        'id' => 'applications.app_state',
                        'field' => 'applications.app_state',
                        'type' => 'string',
                        'input' => 'text',
                        'operator' => 'not_equal',
                        'value' => 'OK',
                    ],
                ],
            ],
        ]);

        new AlertRules($device)->run();
        $this->assertFaultState($device, $rule, AlertState::ACTIVE);

        // Purge removes the row entirely, as done by the daily applications_purge task.
        $application->delete();
        Application::purgeTrashed(0);

        new AlertRules($device)->run();

        $this->assertFaultState($device, $rule, AlertState::RECOVERED);
    }

    private function assertFaultState(Device $device, AlertRule $rule, int $state): void
    {
        $fault = AlertFault::query()
            ->where('device_id', $device->device_id)
            ->where('rule_id', $rule->id)
            ->first();

        $this->assertNotNull($fault, 'A fault should exist for the rule');
        $this->assertSame($state, (int) $fault->state);
    }
}
