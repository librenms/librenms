<?php

namespace App\Actions\Device;

use App\Models\Device;
use SnmpQuery;

class DeviceSnmpIsAvailable
{
    public function execute(Device $device): bool
    {
        $response = SnmpQuery::device($device)->get('SNMPv2-MIB::sysObjectID.0');

        return $response->getExitCode() === 0 || $response->getExitCode() === 2 || $response->isValid();
    }
}
