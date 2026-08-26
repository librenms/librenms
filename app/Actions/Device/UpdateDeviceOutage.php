<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DeviceOutage;
use LibreNMS\Enum\MaintenanceStatus;

class UpdateDeviceOutage
{
    public function execute(Device $device): void
    {
        if ($device->status) {
            // Device is up: always close any open outages, even during maintenance.
            // Maintenance controls whether new outages are opened, not whether
            // existing ones are closed. Skipping the close here leaves outage rows
            // with up_again=NULL permanently if the device recovers while a
            // maintenance window is active, because subsequent polls see no status
            // change and never call this method again.
            $device->outages()->whereNull('up_again')->update(['up_again' => time()]);

            return;
        }

        // Device is down: skip opening a new outage when maintenance is suppressing
        // availability tracking, so maintenance periods do not inflate outage counts.
        if (LibrenmsConfig::get('graphing.availability_consider_maintenance')
            && $device->getMaintenanceStatus() !== MaintenanceStatus::None) {
            return;
        }

        // Only open a new outage if none is currently open
        if ($device->getCurrentOutage() === null) {
            $device->outages()->save(new DeviceOutage(['going_down' => time()]));
        }
    }
}
