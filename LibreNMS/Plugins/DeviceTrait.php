<?php

namespace LibreNMS\Plugins;

use App\Models\Device;

trait DeviceTrait
{
    protected static $device_view = 'device_overview';

    final public static function device_overview_container($device)
    {
        echo view(self::prefix() . self::$device_view, self::deviceData(Device::find($device['device_id'])));
    }

    protected static function deviceData(Device $device): array
    {
        return [
            'title' => self::className(),
	    'device' => $device,
        ];
    }
}
