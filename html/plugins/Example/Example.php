<?php

namespace LibreNMS\Plugins;

use App\Models\Device;
use App\Models\Port;

class Example extends Plugin
{
    /* Default defined in abstract Plugin class  */
    public function menu()
    {
        echo view(self::viewPath(), [
            'title' => self::className(),
        ]);
    }

    /* Default defined in abstract Plugin class  */
    public function settings()
    {
        echo view(self::viewPath(), [
            'title' => self::className(),
        ]);
    }

    public function device_overview_container($device)
    {
        echo view(self::viewPath(), [
            'title' =>   self::className(),
            'device' => Device::find($device['device_id']),
        ]);
    }

    public function port_container($device, $port)
    {
        echo view(self::viewPath(), [
            'title' => self::className(),
            'port' => Port::find($port['port_id']),
	]);
    }
}
