<?php

namespace LibreNMS\Plugins;

use App\Models\Device;
use App\Models\Port;

class Test extends Plugin
{

    /* Default defined in abstract Plugin class  */
    public function menu()
    {
	$name = self::className();
	echo view(self::viewPath(), compact('name'));
    }

    /* Default defined in abstract Plugin class  */
    public function plugin()
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
	$name = self::className();
        $port = Port::find($port['port_id']);
        echo view(self::viewPath(), compact('name', 'port'));
    }
}
