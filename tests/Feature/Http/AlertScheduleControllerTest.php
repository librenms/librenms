<?php

namespace LibreNMS\Tests\Feature\Http;

use App\Models\AlertSchedule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\User;
use App\Models\UserPref;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\MaintenanceBehavior;
use LibreNMS\Tests\TestCase;
use Spatie\Permission\Models\Role;

final class AlertScheduleControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    private function user(): User
    {
        $user = User::factory()->create(['enabled' => 1]);
        $user->assignRole('user');

        return $user;
    }

    public function testAdminCanStartDeviceMaintenance(): void
    {
        Carbon::setTestNow('2026-10-02 12:00:00');
        $device = Device::factory()->create();

        $response = $this->actingAs($this->admin())
            ->postJson(route('alert-schedule.store'), [
                'title' => 'Device maintenance',
                'notes' => 'Swapping PSU',
                'behavior' => MaintenanceBehavior::MuteAlerts->value,
                'recurring' => 0,
                'start' => Carbon::now()->toIso8601String(),
                'duration' => '1:30',
                'maps' => [$device->device_id],
            ])
            ->assertCreated()
            ->assertJsonStructure(['message', 'schedule_id']);

        $schedule = AlertSchedule::findOrFail($response->json('schedule_id'));
        $this->assertSame('Device maintenance', $schedule->title);
        $this->assertSame('Swapping PSU', $schedule->notes);
        $this->assertEquals(MaintenanceBehavior::MuteAlerts->value, $schedule->behavior);
        $this->assertEquals(0, $schedule->recurring);
        $this->assertTrue($schedule->start->equalTo(Carbon::now()));
        $this->assertTrue($schedule->end->equalTo(Carbon::now()->addMinutes(90)));
        $this->assertEquals([$device->device_id], $schedule->devices()->pluck('devices.device_id')->all());
        $this->assertTrue($device->alertSchedules()->isActive()->exists());
    }

    public function testStoreMapsDevicesGroupsAndLocations(): void
    {
        $device = Device::factory()->create();
        $group = DeviceGroup::factory()->create();
        $location = Location::factory()->create();

        $response = $this->actingAs($this->admin())
            ->postJson(route('alert-schedule.store'), [
                'title' => 'Mixed',
                'notes' => '',
                'behavior' => MaintenanceBehavior::SkipAlerts->value,
                'start' => '2026-10-02 12:00',
                'end' => '2026-10-02 14:00',
                'maps' => [(string) $device->device_id, 'g' . $group->id, 'l' . $location->id],
            ])
            ->assertCreated();

        $schedule = AlertSchedule::findOrFail($response->json('schedule_id'));
        $this->assertEquals([$device->device_id], $schedule->devices()->pluck('devices.device_id')->all());
        $this->assertEquals([$group->id], $schedule->deviceGroups()->pluck('device_groups.id')->all());
        $this->assertEquals([$location->id], $schedule->locations()->pluck('locations.id')->all());
    }

    public function testStoreRecurringSchedule(): void
    {
        $device = Device::factory()->create();

        $response = $this->actingAs($this->admin())
            ->postJson(route('alert-schedule.store'), [
                'title' => 'Nightly',
                'behavior' => MaintenanceBehavior::SkipAlerts->value,
                'recurring' => 1,
                'start_recurring_dt' => '2026-10-02',
                'start_recurring_hr' => '23:00',
                'end_recurring_hr' => '01:00',
                'recurring_day' => [1, 3, 5],
                'maps' => [$device->device_id],
            ])
            ->assertCreated();

        $schedule = AlertSchedule::findOrFail($response->json('schedule_id'));
        $this->assertEquals(1, $schedule->recurring);
        $this->assertSame('2026-10-02', $schedule->start_recurring_dt);
        $this->assertSame('23:00', $schedule->start_recurring_hr);
        $this->assertNull($schedule->end_recurring_dt);
        $this->assertSame('01:00', $schedule->end_recurring_hr);
        $this->assertSame(['Mo', 'We', 'Fr'], $schedule->recurring_day);
    }

    public function testStoreAppendsNoteToDevicesWhenPreferenceEnabled(): void
    {
        Carbon::setTestNow('2026-10-02 12:00:00');
        $admin = $this->admin();
        UserPref::setPref($admin, 'add_schedule_note_to_device', 1);
        $device = Device::factory()->create(['notes' => 'existing']);

        $this->actingAs($admin)
            ->postJson(route('alert-schedule.store'), [
                'title' => 'Noted',
                'notes' => 'Upgrading firmware',
                'behavior' => MaintenanceBehavior::SkipAlerts->value,
                'start' => '2026-10-02T12:00:00Z',
                'duration' => '0:30',
                'maps' => [$device->device_id],
            ])
            ->assertCreated();

        $this->assertSame("existing\n2026-10-02 12:00 Alerts delayed: Upgrading firmware", $device->fresh()->notes);
    }

    public function testStoreValidatesInput(): void
    {
        $schedule_count = AlertSchedule::count();
        $this->actingAs($this->admin())
            ->postJson(route('alert-schedule.store'), [
                'behavior' => 9,
                'start' => 'not a date',
                'maps' => ['x1'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'behavior', 'start', 'end', 'maps.0']);

        $this->actingAs($this->admin())
            ->postJson(route('alert-schedule.store'), [
                'title' => 'Recurring',
                'behavior' => MaintenanceBehavior::SkipAlerts->value,
                'recurring' => 1,
                'maps' => [1],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['start_recurring_dt', 'start_recurring_hr', 'end_recurring_hr'])
            ->assertJsonMissingValidationErrors(['start', 'end']);

        $this->assertSame($schedule_count, AlertSchedule::count());
    }

    public function testUserCannotStartMaintenance(): void
    {
        $schedule_count = AlertSchedule::count();
        $device = Device::factory()->create();

        $this->actingAs($this->user())
            ->postJson(route('alert-schedule.store'), [
                'title' => 'Nope',
                'behavior' => MaintenanceBehavior::SkipAlerts->value,
                'start' => '2026-10-02T12:00:00Z',
                'duration' => '0:30',
                'maps' => [$device->device_id],
            ])
            ->assertForbidden();

        $this->assertSame($schedule_count, AlertSchedule::count());
    }

    public function testAdminCanEndMaintenance(): void
    {
        Carbon::setTestNow('2026-10-02 12:00:00');
        $device = Device::factory()->create();
        $schedule = AlertSchedule::factory()->create([
            'start' => '2026-10-02 11:00:00',
            'end' => '2026-10-02 14:00:00',
        ]);
        $schedule->devices()->attach($device);
        $this->assertTrue($device->alertSchedules()->isActive()->exists());

        $this->actingAs($this->admin())
            ->postJson(route('alert-schedule.end', $schedule))
            ->assertOk()
            ->assertJson(['message' => 'Maintenance has been ended']);

        $this->assertTrue($schedule->fresh()->end->equalTo(Carbon::now()));
    }

    public function testUserCannotEndMaintenance(): void
    {
        $schedule = AlertSchedule::factory()->create();

        $this->actingAs($this->user())
            ->postJson(route('alert-schedule.end', $schedule))
            ->assertForbidden();
    }

    public function testShowReturnsScheduleWithTargets(): void
    {
        $device = Device::factory()->create();
        $group = DeviceGroup::factory()->create(['name' => 'Core']);
        $location = Location::factory()->create(['location' => 'Rack 1']);
        $schedule = AlertSchedule::factory()->create([
            'title' => 'Weekly',
            'recurring' => 1,
            'start' => '2026-10-02 23:00:00',
            'end' => '9000-09-09 01:00:00',
            'recurring_day' => '1,7',
        ]);
        $schedule->devices()->attach($device);
        $schedule->deviceGroups()->attach($group);
        $schedule->locations()->attach($location);

        $this->actingAs($this->admin())
            ->getJson(route('alert-schedule.show', $schedule))
            ->assertOk()
            ->assertJson([
                'title' => 'Weekly',
                'recurring' => 1,
                'recurring_day' => ['Mo', 'Su'],
                'start_recurring_hr' => '23:00',
                'end_recurring_dt' => null,
                'targets' => [
                    ['id' => $device->device_id, 'text' => $device->display],
                    ['id' => 'g' . $group->id, 'text' => 'Core'],
                    ['id' => 'l' . $location->id, 'text' => 'Rack 1'],
                ],
            ]);
    }

    public function testUserCannotShowSchedule(): void
    {
        $schedule = AlertSchedule::factory()->create();

        $this->actingAs($this->user())
            ->getJson(route('alert-schedule.show', $schedule))
            ->assertForbidden();
    }

    public function testAdminCanUpdateSchedule(): void
    {
        $schedule_count = AlertSchedule::count();
        $old_device = Device::factory()->create();
        $new_device = Device::factory()->create();
        $schedule = AlertSchedule::factory()->create([
            'title' => 'Old',
            'recurring' => 1,
            'recurring_day' => '1',
        ]);
        $schedule->devices()->attach($old_device);

        $this->actingAs($this->admin())
            ->putJson(route('alert-schedule.update', $schedule), [
                'title' => 'New',
                'notes' => 'changed',
                'behavior' => MaintenanceBehavior::RunAlerts->value,
                'start' => '2026-10-02 12:00',
                'end' => '2026-10-02 14:00',
                'maps' => [$new_device->device_id],
            ])
            ->assertOk()
            ->assertJson(['schedule_id' => $schedule->schedule_id]);

        $schedule->refresh();
        $this->assertSame('New', $schedule->title);
        $this->assertSame('changed', $schedule->notes);
        $this->assertEquals(MaintenanceBehavior::RunAlerts->value, $schedule->behavior);
        $this->assertEquals(0, $schedule->recurring);
        $this->assertSame([], $schedule->recurring_day);
        $this->assertEquals([$new_device->device_id], $schedule->devices()->pluck('devices.device_id')->all());
        $this->assertSame($schedule_count + 1, AlertSchedule::count()); // updated in place, not duplicated
    }

    public function testUserCannotUpdateSchedule(): void
    {
        $schedule = AlertSchedule::factory()->create(['title' => 'Old']);

        $this->actingAs($this->user())
            ->putJson(route('alert-schedule.update', $schedule), [
                'title' => 'New',
                'behavior' => MaintenanceBehavior::SkipAlerts->value,
                'start' => '2026-10-02 12:00',
                'end' => '2026-10-02 14:00',
                'maps' => [1],
            ])
            ->assertForbidden();

        $this->assertSame('Old', $schedule->fresh()->title);
    }

    public function testAdminCanDeleteSchedule(): void
    {
        $device = Device::factory()->create();
        $schedule = AlertSchedule::factory()->create();
        $schedule->devices()->attach($device);

        $this->actingAs($this->admin())
            ->deleteJson(route('alert-schedule.destroy', $schedule))
            ->assertOk();

        $this->assertNull(AlertSchedule::find($schedule->schedule_id));
        $this->assertSame(0, DB::table('alert_schedulables')->where('schedule_id', $schedule->schedule_id)->count());
    }

    public function testUserCannotDeleteSchedule(): void
    {
        $schedule = AlertSchedule::factory()->create();

        $this->actingAs($this->user())
            ->deleteJson(route('alert-schedule.destroy', $schedule))
            ->assertForbidden();

        $this->assertNotNull($schedule->fresh());
    }

    public function testDeviceEditPageRendersMaintenanceButton(): void
    {
        $device = Device::factory()->create();

        $this->actingAs($this->admin())
            ->get(route('device.edit', $device))
            ->assertOk()
            ->assertSee('id="maintenance"', false)
            ->assertSee("route('alert-schedule.end'", false)
            ->assertDontSee('<maintenance-mode', false);
    }
}
