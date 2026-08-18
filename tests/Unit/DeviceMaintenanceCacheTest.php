<?php

namespace LibreNMS\Tests\Unit;

use App\Models\AlertSchedule;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Cache\DeviceMaintenanceCache;
use LibreNMS\Enum\MaintenanceBehavior;
use LibreNMS\Enum\MaintenanceStatus;
use LibreNMS\Tests\DBTestCase;

final class DeviceMaintenanceCacheTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testReturnsNoneWhenNoSchedulesActive(): void
    {
        $device = Device::factory()->create();

        $cache = new DeviceMaintenanceCache();
        $this->assertEquals(MaintenanceStatus::None, $cache->statusFor($device->device_id));
        $this->assertEquals(MaintenanceStatus::None, $device->getMaintenanceStatus());
    }

    public function testDirectDeviceMaintenanceSchedule(): void
    {
        $device = Device::factory()->create();
        $schedule = AlertSchedule::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => MaintenanceBehavior::MuteAlerts,
        ]);
        $schedule->devices()->attach($device);

        $cache = new DeviceMaintenanceCache();
        $this->assertEquals(MaintenanceStatus::MuteAlerts, $cache->statusFor($device->device_id));
        $this->assertEquals(MaintenanceStatus::MuteAlerts, $device->getMaintenanceStatus());
    }

    public function testLocationMaintenanceSchedule(): void
    {
        $location = Location::factory()->create();
        $device = Device::factory()->create(['location_id' => $location->id]);
        $schedule = AlertSchedule::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => MaintenanceBehavior::SkipAlerts,
        ]);
        $schedule->locations()->attach($location);

        $cache = new DeviceMaintenanceCache();
        $this->assertEquals(MaintenanceStatus::SkipAlerts, $cache->statusFor($device->device_id));
        $this->assertEquals(MaintenanceStatus::SkipAlerts, $device->getMaintenanceStatus());
    }

    public function testDeviceGroupMaintenanceSchedule(): void
    {
        $group = DeviceGroup::factory()->create();
        $device = Device::factory()->create();
        $group->devices()->attach($device);

        $schedule = AlertSchedule::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => MaintenanceBehavior::RunAlerts,
        ]);
        $schedule->deviceGroups()->attach($group);

        $cache = new DeviceMaintenanceCache();
        $this->assertEquals(MaintenanceStatus::RunAlerts, $cache->statusFor($device->device_id));
        $this->assertEquals(MaintenanceStatus::RunAlerts, $device->getMaintenanceStatus());
    }
}
