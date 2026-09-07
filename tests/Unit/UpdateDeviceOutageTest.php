<?php

namespace LibreNMS\Tests\Unit;

use App\Actions\Device\UpdateDeviceOutage;
use App\Facades\LibrenmsConfig;
use App\Models\AlertSchedule;
use App\Models\Device;
use App\Models\DeviceOutage;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\MaintenanceBehavior;
use LibreNMS\Tests\DBTestCase;

final class UpdateDeviceOutageTest extends DBTestCase
{
    use DatabaseTransactions;

    private UpdateDeviceOutage $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(UpdateDeviceOutage::class);
    }

    public function testClosesOpenOutageWhenDeviceComesUp(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        DeviceOutage::factory()->open()->create(['device_id' => $device->device_id]);

        $this->action->execute($device);

        $this->assertNull($device->outages()->whereNull('up_again')->first());
        $this->assertNotNull($device->outages()->first()->up_again);
    }

    public function testClosesOpenOutageWhenDeviceComesUpDuringMaintenance(): void
    {
        LibrenmsConfig::set('graphing.availability_consider_maintenance', true);

        $device = Device::factory()->create(['status' => 1]);
        DeviceOutage::factory()->open()->create(['device_id' => $device->device_id]);

        $schedule = AlertSchedule::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => MaintenanceBehavior::MuteAlerts,
        ]);
        $schedule->devices()->attach($device);

        $this->action->execute($device);

        $this->assertNull(
            $device->outages()->whereNull('up_again')->first(),
            'Outage should be closed even when device is in maintenance'
        );
        $this->assertNotNull($device->outages()->first()->up_again);
    }

    public function testDoesNothingWhenDeviceUpWithNoOpenOutage(): void
    {
        $device = Device::factory()->create(['status' => 1]);
        DeviceOutage::factory()->closed()->create(['device_id' => $device->device_id]);

        $this->action->execute($device);

        $this->assertCount(1, $device->outages);
        $this->assertNotNull($device->outages()->first()->up_again);
    }

    public function testOpensNewOutageWhenDeviceGoesDown(): void
    {
        $device = Device::factory()->create(['status' => 0]);

        $this->action->execute($device);

        $this->assertCount(1, $device->outages()->get());
        $this->assertNull($device->outages()->first()->up_again);
    }

    public function testDoesNotOpenDuplicateOutageWhenOneAlreadyOpen(): void
    {
        $device = Device::factory()->create(['status' => 0]);
        DeviceOutage::factory()->open()->create(['device_id' => $device->device_id]);

        $this->action->execute($device);

        $this->assertCount(1, $device->outages()->get());
    }

    public function testDoesNotOpenOutageDuringMaintenanceWhenConsiderMaintenanceEnabled(): void
    {
        LibrenmsConfig::set('graphing.availability_consider_maintenance', true);

        $device = Device::factory()->create(['status' => 0]);
        $schedule = AlertSchedule::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => MaintenanceBehavior::MuteAlerts,
        ]);
        $schedule->devices()->attach($device);

        $this->action->execute($device);

        $this->assertCount(0, $device->outages()->get());
    }

    public function testOpensOutageDuringMaintenanceWhenConsiderMaintenanceDisabled(): void
    {
        LibrenmsConfig::set('graphing.availability_consider_maintenance', false);

        $device = Device::factory()->create(['status' => 0]);
        $schedule = AlertSchedule::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
            'behavior' => MaintenanceBehavior::MuteAlerts,
        ]);
        $schedule->devices()->attach($device);

        $this->action->execute($device);

        $this->assertCount(1, $device->outages()->get());
        $this->assertNull($device->outages()->first()->up_again);
    }
}
