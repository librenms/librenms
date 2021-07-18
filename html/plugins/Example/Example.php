<?php

namespace LibreNMS\Plugins;

use App\Models\Device;
use App\Models\Port;

class Example extends Plugin
{
    /* Default defined in abstract Plugin class  */
    public function menuData(): array
    {
        return [
            'title' => $this->className(),
        ];
    }

    /* Default defined in abstract Plugin class  */
    public function settingsData(): array
    {
        return [
            'title' => $this->className(),
        ];
    }

    public function deviceData($device)
    {
        return [
            'title' => $this->className(),
            'device' => $device->find($device['device_id']),
        ];
    }

    public function portData($device, $port)
    {
        return [
            'title' => $this->className(),
            'port' => $port->find($port['port_id']),
        ];
    }
}
