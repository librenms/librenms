<?php

/*
 * Scheduled maintenance (alert schedule) filter of the faults page.
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
 */

namespace LibreNMS\Tests\Feature;

use App\Models\AlertFault;
use App\Models\AlertRule;
use App\Models\AlertSchedule;
use App\Models\Dashboard;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use App\Models\User;
use App\Models\UserWidget;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\MaintenanceBehavior;
use LibreNMS\Tests\DBTestCase;
use Spatie\Permission\Models\Role;

/**
 * The scheduled_maintenance fault filter hides the faults of devices inside an active
 * maintenance, whether it targets the device, its location or one of its device groups.
 */
final class AlertFaultScheduledMaintenanceTest extends DBTestCase
{
    use DatabaseTransactions;

    // 2026-10-02 is a Friday, the frozen time is 12:00 in UTC
    private const string NOW = '2026-10-02 12:00:00';

    private ?string $timezone = null;

    protected function setUp(): void
    {
        parent::setUp();

        // alert schedules compare the stored utc time with the local time of day, pin both to utc
        $this->timezone = config('app.timezone');
        config(['app.timezone' => 'UTC']);
        date_default_timezone_set('UTC');
        Carbon::setTestNow(self::NOW);
        CarbonImmutable::setTestNow(self::NOW);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        config(['app.timezone' => $this->timezone]);
        date_default_timezone_set((string) $this->timezone);

        parent::tearDown();
    }

    public function testDevicesUnderActiveMaintenancesAreFiltered(): void
    {
        // the database can hold unrelated faults and maintenances, so every assertion only looks at the devices created below
        $rule = AlertRule::factory()->create();
        $location = Location::factory()->create();
        $group = DeviceGroup::factory()->create(['name' => 'maintenance group']);

        $direct = Device::factory()->create();
        $inLocation = Device::factory()->create(['location_id' => $location->id]);
        $inGroup = Device::factory()->create();
        $noMaintenance = Device::factory()->create();
        $group->devices()->attach($inGroup);

        foreach ([$direct, $inLocation, $inGroup, $noMaintenance] as $device) {
            $this->createFault($rule, $device);
        }

        $this->createSchedule(['devices' => [$direct]]);
        $this->createSchedule(['locations' => [$location]]);
        $this->createSchedule(['groups' => [$group]]);

        $created = [$direct, $inLocation, $inGroup, $noMaintenance];

        $this->assertSameDevices([$direct, $inLocation, $inGroup], $this->faultDeviceIds('eq', $created));
        $this->assertSameDevices([$noMaintenance], $this->faultDeviceIds('neq', $created));
    }

    public function testSchedulesOutsideTheirWindowDoNotFilter(): void
    {
        $rule = AlertRule::factory()->create();
        $location = Location::factory()->create();
        $group = DeviceGroup::factory()->create(['name' => 'maintenance group']);

        $past = Device::factory()->create();
        $future = Device::factory()->create();
        $inLocation = Device::factory()->create(['location_id' => $location->id]);
        $inGroup = Device::factory()->create();
        $group->devices()->attach($inGroup);
        foreach ([$past, $future, $inLocation, $inGroup] as $device) {
            $this->createFault($rule, $device);
        }

        // every device is targeted below, but outside the maintenance window
        $now = CarbonImmutable::now();
        $expired = $this->createSchedule(['devices' => [$past]], $now->subDays(3), $now->subDay());
        $this->createSchedule(['locations' => [$location]], $now->subDays(3), $now->subDay());
        $this->createSchedule(['groups' => [$group]], $now->addDay(), $now->addDays(2));
        $upcoming = $this->createSchedule(['devices' => [$future]], $now->addDay(), $now->addDays(2));

        $this->assertFalse(AlertSchedule::isActive()->where('schedule_id', $expired->schedule_id)->exists(), 'the expired maintenance should be inactive');
        $this->assertFalse(AlertSchedule::isActive()->where('schedule_id', $upcoming->schedule_id)->exists(), 'the upcoming maintenance should not be active yet');

        $created = [$past, $future, $inLocation, $inGroup];

        $this->assertSameDevices([], $this->faultDeviceIds('eq', $created));
        $this->assertSameDevices($created, $this->faultDeviceIds('neq', $created));
    }

    public function testRecurringScheduleOnlyFiltersOnItsDays(): void
    {
        $rule = AlertRule::factory()->create();
        $friday = Device::factory()->create();
        $wednesday = Device::factory()->create();
        $this->createFault($rule, $friday);
        $this->createFault($rule, $wednesday);

        // every Friday and every Wednesday, 10:00 - 14:00. The frozen time is a Friday at noon,
        // so only the Friday maintenance is active.
        $fridaySchedule = $this->createRecurringSchedule($friday, '5');
        $wednesdaySchedule = $this->createRecurringSchedule($wednesday, '3');

        $this->assertTrue(AlertSchedule::isActive()->where('schedule_id', $fridaySchedule->schedule_id)->exists(), 'the Friday maintenance should be active');
        $this->assertFalse(AlertSchedule::isActive()->where('schedule_id', $wednesdaySchedule->schedule_id)->exists(), 'the Wednesday maintenance should not be active yet');

        $created = [$friday, $wednesday];

        $this->assertSameDevices([$friday], $this->faultDeviceIds('eq', $created));
        $this->assertSameDevices([$wednesday], $this->faultDeviceIds('neq', $created));
    }

    public function testSchedulesStoredAsClassNameFilter(): void
    {
        // older installs store the alert_schedulable_type class name instead of the morph alias
        $rule = AlertRule::factory()->create();
        $device = Device::factory()->create();
        $this->createFault($rule, $device);

        $schedule = $this->createSchedule(['devices' => [$device]]);
        DB::table('alert_schedulables')
            ->where('schedule_id', $schedule->schedule_id)
            ->update(['alert_schedulable_type' => Device::class]);

        $this->assertSameDevices([$device], $this->faultDeviceIds('eq', [$device]));
    }

    public function testAlertsWidgetSettingFiltersFaults(): void
    {
        // the alerts widget posts its flat scheduled_maintenance setting to the alerts table
        $admin = $this->admin();
        $rule = AlertRule::factory()->create();
        $location = Location::factory()->create();

        $maintained = Device::factory()->create(['disabled' => 0, 'location_id' => $location->id]);
        $up = Device::factory()->create(['disabled' => 0]);
        $maintainedFault = (int) $this->createFault($rule, $maintained)->id;
        $upFault = (int) $this->createFault($rule, $up)->id;
        $this->createSchedule(['locations' => [$location]]);

        // only the faults created above, an existing database can hold unrelated faults
        $this->assertSame([$maintainedFault], $this->widgetFaultIds($admin, '1', [$maintainedFault, $upFault]));
        $this->assertSame([$upFault], $this->widgetFaultIds($admin, '0', [$maintainedFault, $upFault]));
        // "not filtered" is sent as an empty value
        $this->assertEqualsCanonicalizing(
            [$maintainedFault, $upFault],
            $this->widgetFaultIds($admin, '', [$maintainedFault, $upFault])
        );

        $this->actingAs($admin)->postJson(route('table.alerts'), ['scheduled_maintenance' => 'bogus'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('scheduled_maintenance');
    }

    private function admin(): User
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create(['enabled' => 1]);
        $admin->assignRole('admin');

        return $admin;
    }

    public function testAlertsWidgetSettingsFormKeepsHiddenMaintenanceSelected(): void
    {
        $this->assertMaintenanceOptionSelected('0', 'hide alerts on devices in a scheduled maintenance');
    }

    public function testAlertsWidgetSettingsFormKeepsShownMaintenanceSelected(): void
    {
        $this->assertMaintenanceOptionSelected('1', 'show only alerts on devices in a scheduled maintenance');
    }

    public function testAlertsWidgetSendsItsMaintenanceSettingToTheAlertsTable(): void
    {
        $html = $this->renderWidget($this->admin(), ['scheduled_maintenance' => '1']);

        $this->assertStringContainsString('scheduled_maintenance: "1"', $html);
    }

    private function assertMaintenanceOptionSelected(string $value, string $label): void
    {
        $html = $this->renderWidget($this->admin(), ['scheduled_maintenance' => $value], settingsForm: true);

        // blade puts whitespace around its if, ignore the formatting; only look at this filter's
        // select since other fields offer 0 and 1 values too
        $select = Str::before(Str::after((string) preg_replace('/\s+/', ' ', $html), 'name="scheduled_maintenance"'), '</select>');

        $this->assertNotSame('', $select, 'the widget settings form has no scheduled maintenance field');
        $this->assertStringContainsString('<option value="' . $value . '" selected >' . $label . '</option>', $select);
        $this->assertStringNotContainsString('<option value="' . ($value === '0' ? '1' : '0') . '" selected >', $select);
    }

    /**
     * Create an alerts widget with these settings and render it. WidgetController keeps the settings
     * it read first, so a test must not render a second widget.
     *
     * @param  array<string, mixed>  $settings
     */
    private function renderWidget(User $admin, array $settings, bool $settingsForm = false): string
    {
        $dashboard = Dashboard::create([
            'user_id' => $admin->user_id,
            'dashboard_name' => 'maintenance test',
            'access' => 1,
        ]);
        $widget = UserWidget::create([
            'user_id' => $admin->user_id,
            'widget' => 'alerts',
            'col' => 0,
            'row' => 0,
            'size_x' => 4,
            'size_y' => 3,
            'title' => '',
            'refresh' => 60,
            'settings' => $settings,
            'dashboard_id' => $dashboard->dashboard_id,
        ]);

        $params = ['id' => $widget->user_widget_id];
        if ($settingsForm) {
            $params['settings'] = 1;
        }

        return (string) $this->actingAs($admin)
            ->postJson(route('widget.alerts'), $params)
            ->assertOk()
            ->json('html');
    }

    /**
     * Fault ids the alerts table returns for a widget setting, limited to the ids of this test
     *
     * @param  list<int>  $only
     * @return list<int>
     */
    private function widgetFaultIds(User $admin, string $setting, array $only): array
    {
        $rows = $this->actingAs($admin)
            ->postJson(route('table.alerts'), ['scheduled_maintenance' => $setting, 'rowCount' => -1])
            ->assertOk()
            ->json('rows') ?? [];

        return array_values(array_intersect($only, array_map(fn (array $row) => (int) $row['alert_id'], $rows)));
    }

    /**
     * Recurring maintenance on the given day of week (1 - 7), 10:00 - 14:00, from 2026 until year 9000
     */
    private function createRecurringSchedule(Device $device, string $dayOfWeek): AlertSchedule
    {
        $schedule = $this->createSchedule(
            ['devices' => [$device]],
            CarbonImmutable::parse('2026-01-01 10:00:00'),
            CarbonImmutable::create(9000, 9, 9, 14, 0)
        );
        $schedule->recurring = true;
        $schedule->recurring_day = $dayOfWeek;
        $schedule->save();

        return $schedule;
    }

    /**
     * Create an active maintenance (an hour around the frozen test time) for the given targets
     *
     * @param  array{devices?: list<Device>, groups?: list<DeviceGroup>, locations?: list<Location>}  $targets
     */
    private function createSchedule(array $targets, ?CarbonImmutable $start = null, ?CarbonImmutable $end = null): AlertSchedule
    {
        $schedule = new AlertSchedule(['title' => 'maintenance', 'notes' => '', 'behavior' => MaintenanceBehavior::MuteAlerts]);
        $schedule->start = $start ?: CarbonImmutable::now()->subHour();
        $schedule->end = $end ?: CarbonImmutable::now()->addHour();
        $schedule->save();

        $schedule->devices()->attach($targets['devices'] ?? []);
        $schedule->deviceGroups()->attach($targets['groups'] ?? []);
        $schedule->locations()->attach($targets['locations'] ?? []);

        return $schedule;
    }

    private function createFault(AlertRule $rule, Device $device): AlertFault
    {
        return AlertFault::create([
            'rule_id' => $rule->id,
            'device_id' => $device->device_id,
            'entity_key' => (string) $device->device_id,
            'state' => AlertState::ACTIVE,
            'open' => 1,
        ]);
    }

    /**
     * Devices of the faults matching the filter, limited to $only so unrelated
     * faults and maintenances of an existing database cannot change the result
     *
     * @param  string  $operator  eq (is) or neq (is not)
     * @param  list<Device>  $only
     * @return list<int>
     */
    private function faultDeviceIds(string $operator, array $only): array
    {
        return AlertFault::query()
            ->applyFilters(['scheduled_maintenance' => [$operator => '1']])
            ->whereIn('alert_faults.device_id', array_map(fn (Device $device) => $device->device_id, $only))
            ->pluck('device_id')
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  list<Device>  $expected
     * @param  list<int>  $actual
     */
    private function assertSameDevices(array $expected, array $actual): void
    {
        $this->assertEqualsCanonicalizing(array_map(fn (Device $device) => $device->device_id, $expected), $actual);
    }
}
