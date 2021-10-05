<?php

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Poweralert extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $version = snmp_get($this->getDeviceArray(), 'upsIdentAgentSoftwareVersion.0', '-Ovq', 'UPS-MIB');
        $device->version = $version;
    }
}
