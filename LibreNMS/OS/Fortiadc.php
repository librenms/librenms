<?php

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS\Shared\Fortinet;

class Fortiadc extends Fortinet implements OSPolling
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $device->hardware = $device->hardware ?: $this->getHardwareName();
    }

    public function pollOS(): void
    {
        //
    }
}
