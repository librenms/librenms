<?php

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DeviceOutage;
use LibreNMS\Enum\MaintenanceStatus;

class UpdateDeviceOutage
{
    public function execute(Device $device, bool $available): void
    {
        if (LibrenmsConfig::get('graphing.availability_consider_maintenance')
            && $device->getMaintenanceStatus() !== MaintenanceStatus::NONE) {
            return;
        }

        if ($available) {
            // Device is up, close any open outages
            $device->outages()->whereNull('up_again')->get()->each(function (DeviceOutage $outage): void {
                $outage->up_again = time();
                $outage->save();
            });

            return;
        }

        // Device is down, only open a new outage if none is currently open
        if ($device->getCurrentOutage() === null) {
            $device->outages()->save(new DeviceOutage(['going_down' => time()]));
        }
    }
}
