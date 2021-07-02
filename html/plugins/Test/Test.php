<?php

namespace LibreNMS\Plugins;

use App\Models\Device;
use App\Models\Port;

class Test
{
    public static function menu()
    {
        $name = self::className();
        echo view(self::viewPath(), compact('name'));
    }

    public function device_overview_container($device)
    {
        $name = self::className() . ' Plugin';
        $device = Device::find($device['device_id']);
        echo view(self::viewPath(), compact('name', 'device'));
    }

    public function port_container($device, $port)
    {
        $name = self::className() . ' Plugin';
        $port = Port::find($port['port_id']);
        echo view(self::viewPath(), compact('name', 'port'));
    }

    private static function viewPath()
    {
        return 'plugins.' . strtolower(self::className()) . '.' . debug_backtrace()[1]['function'];
    }

    private static function className()
    {
        return str_replace(__NAMESPACE__ . '\\', '', self::class);
    }
}
