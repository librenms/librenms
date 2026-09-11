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
            $device->outages()->whereNull('up_again')->update(['up_again' => time()]);

            return;
        }

        if (LibrenmsConfig::get('graphing.availability_consider_maintenance')
            && $device->getMaintenanceStatus() !== MaintenanceStatus::None) {
            return;
        }

        if ($device->getCurrentOutage() === null) {
            $device->outages()->save(new DeviceOutage(['going_down' => time()]));
        }
    }
}
