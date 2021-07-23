<?php

namespace LibreNMS\Plugins;

use App\Models\Device;

trait DeviceHook
{
    public static function device_overview_container($device)
    {
        echo view(self::prefix() . self::authenticateDeviceHook($device), self::deviceData(Device::find($device['device_id'])));
    }

    protected static function authenticateDeviceHook($device)
    {
        return 'device_overview';
    }

    protected static function deviceData(Device $device): array
    {
        return [
            'title' => self::className(),
            'device' => $device,
        ];
    }
}
