<?php

namespace App\Actions\Device;

use App\Models\Device;
use SnmpQuery;

class DeviceIsSnmpable
{
    public function execute(Device $device): bool
    {
        $response = SnmpQuery::device($device)->get('SNMPv2-MIB::sysObjectID.0');

        return $response->getExitCode() === 0 || $response->isValid();
    }
}
