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
            // Close any open outage regardless of maintenance, maintenance only suppresses opening new ones.
            $device->outages()->whereNull('up_again')->update(['up_again' => time()]);

            return;
        }

        // Don't open a new outage when maintenance is suppressing availability tracking.
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
