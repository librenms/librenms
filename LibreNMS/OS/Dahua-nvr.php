<?php

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Dahua-nvr extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        // Dahua_NVR reports the same sysName for each device causing issues with duplicate sysName errors.
        $info = snmp_getnext_multi($this->getDeviceArray(), ['deviceType', 'softwareRevision', 'serialNumber', 'machineName'], '-OQUs', 'DAHUA-SNMP-MIB');
        $device->hardware = $info['deviceType'];
        $device->version = $info['softwareRevision'];
        $device->serial = $info['serialNumber'];
        $device->sysName = $info['machineName'];
    }
}
