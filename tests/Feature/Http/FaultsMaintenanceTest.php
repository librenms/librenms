<?php

/**
 * FaultsMaintenanceTest.php
 *
 * Devices under an active scheduled downtime (maintenance window) must never
 * appear in the /faults list (table.alerts endpoint). Schedules with the
 * RunAlerts behavior keep normal alerting and must not hide anything.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * @link https://www.librenms.org
 */

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AlertFault;
use App\Models\AlertRule;
use App\Models\AlertSchedule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\MaintenanceBehavior;
use LibreNMS\Tests\InMemoryDbTestCase;
use Spatie\Permission\Models\Role;

class FaultsMaintenanceTest extends InMemoryDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
    }

    public static function hidingBehaviors(): \Generator
    {
        yield 'skip_alerts' => [MaintenanceBehavior::SkipAlerts];
        yield 'mute_alerts' => [MaintenanceBehavior::MuteAlerts];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('hidingBehaviors')]
    public function testActiveDirectDeviceScheduleHidesFaults(MaintenanceBehavior $behavior): void
    {
        $fault = $this->makeFault();
        $this->makeSchedule($fault->device, $behavior);

        $this->assertFaultHidden($fault);
    }

    public function testRunAlertsBehaviorKeepsFaultsVisible(): void
    {
        $fault = $this->makeFault();
        $this->makeSchedule($fault->device, MaintenanceBehavior::RunAlerts);

        $this->assertFaultVisible($fault);
    }

    public function testFutureScheduleKeepsFaultsVisible(): void
    {
        $fault = $this->makeFault();
        AlertSchedule::factory()->create([
            'start' => Carbon::now()->addHour(),
            'end' => Carbon::now()->addDays(2),
            'behavior' => MaintenanceBehavior::SkipAlerts,
        ])->devices()->attach($fault->device);

        $this->assertFaultVisible($fault);
    }

    public function testLapsedScheduleKeepsFaultsVisible(): void
    {
        $fault = $this->makeFault();
        AlertSchedule::factory()->create([
            'start' => Carbon::now()->subDays(2),
            'end' => Carbon::now()->subHour(),
            'behavior' => MaintenanceBehavior::SkipAlerts,
        ])->devices()->attach($fault->device);

        $this->assertFaultVisible($fault);
    }

    public function testActiveRecurringScheduleHidesFaults(): void
    {
        $fault = $this->makeFault();
        AlertSchedule::factory()->recurring()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'recurring_day' => (int) Carbon::now()->format('N'),
            'behavior' => MaintenanceBehavior::SkipAlerts,
        ])->devices()->attach($fault->device);

        $this->assertFaultHidden($fault);
    }

    public function testOffDayRecurringScheduleKeepsFaultsVisible(): void
    {
        $fault = $this->makeFault();
        $offDay = ((int) Carbon::now()->format('N') % 7) + 1; // a different weekday than today
        AlertSchedule::factory()->recurring()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'recurring_day' => $offDay,
            'behavior' => MaintenanceBehavior::SkipAlerts,
        ])->devices()->attach($fault->device);

        $this->assertFaultVisible($fault);
    }

    public function testActiveDeviceGroupScheduleHidesFaults(): void
    {
        $fault = $this->makeFault();

        // Explicit, deliberately different ids so the test cannot pass if the lookup
        // mixes up the alert_schedulable_id parent key with the schedule_id key.
        $group = DeviceGroup::factory()->create(['id' => 501]);
        $group->devices()->attach($fault->device_id);

        AlertSchedule::factory()->create([
            'schedule_id' => 901,
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => MaintenanceBehavior::SkipAlerts,
        ])->deviceGroups()->attach($group);

        $this->assertFaultHidden($fault);
    }

    public function testActiveLocationScheduleHidesFaults(): void
    {
        $fault = $this->makeFault();
        $location = Location::factory()->create();
        Device::where('device_id', $fault->device_id)->update(['location_id' => $location->id]);

        AlertSchedule::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => MaintenanceBehavior::MuteAlerts,
        ])->locations()->attach($location);

        $this->assertFaultHidden($fault);
    }

    // ---- helpers ----

    private function makeSchedule(Device $device, MaintenanceBehavior $behavior): AlertSchedule
    {
        $schedule = AlertSchedule::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => $behavior,
        ]);
        $schedule->devices()->attach($device);

        return $schedule;
    }

    private function makeFault(): AlertFault
    {
        $device = Device::factory()->create();
        $rule = AlertRule::factory()->create();

        return AlertFault::create([
            'rule_id' => $rule->id,
            'device_id' => $device->device_id,
            'entity_key' => (string) $device->device_id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
            'alerted' => 1,
            'details' => ['rule' => [['name' => 'test']]],
        ]);
    }

    private function assertFaultHidden(AlertFault $fault): void
    {
        $this->assertNotContains($fault->id, $this->allFaultIds(), 'fault must not be listed during scheduled downtime');
    }

    private function assertFaultVisible(AlertFault $fault): void
    {
        $this->assertContains($fault->id, $this->allFaultIds(), 'fault must be listed');
    }

    /**
     * @return list<int>
     */
    private function allFaultIds(): array
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        $rows = $this->actingAs($admin)
            ->postJson(route('table.alerts'))
            ->assertOk()
            ->json('rows');

        return array_map(intval(...), array_values(array_column((array) $rows, 'alert_id')));
    }
}
