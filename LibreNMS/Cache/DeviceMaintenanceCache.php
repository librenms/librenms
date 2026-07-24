<?php

namespace LibreNMS\Cache;

use App\Models\AlertSchedule;
use Illuminate\Container\Attributes\Scoped;
use LibreNMS\Enum\MaintenanceStatus;

#[Scoped]
class DeviceMaintenanceCache
{
    /** @var array<int, MaintenanceStatus>|null */
    private ?array $statuses = null;

    public function statusFor(int $deviceId): MaintenanceStatus
    {
        $this->statuses ??= $this->build();

        return $this->statuses[$deviceId] ?? MaintenanceStatus::None;
    }

    private function build(): array
    {
        $statuses = [];

        $schedules = AlertSchedule::isActive()
            ->with([
                'devices:device_id',
                'locations:id',
                'locations.devices:device_id,location_id',
                'deviceGroups:id',
                'deviceGroups.devices:device_id',
            ])
            ->get(['schedule_id', 'behavior']);

        foreach ($schedules as $schedule) {
            $status = MaintenanceStatus::fromBehavior($schedule->behavior);

            foreach ($schedule->devices as $device) {
                $statuses[$device->device_id] = $status;
            }
            foreach ($schedule->locations as $location) {
                foreach ($location->devices as $device) {
                    $statuses[$device->device_id] = $status;
                }
            }
            foreach ($schedule->deviceGroups as $group) {
                foreach ($group->devices as $device) {
                    $statuses[$device->device_id] = $status;
                }
            }
        }

        return $statuses;
    }
}
