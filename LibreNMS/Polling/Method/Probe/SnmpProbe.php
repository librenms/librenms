<?php

namespace LibreNMS\Polling\Method\Probe;

use App\Models\Device;
use LibreNMS\Interfaces\PollingMethodProbeInterface;
use SnmpQuery;

final readonly class SnmpProbe implements PollingMethodProbeInterface
{
    public function check(Device $device): ProbeResult
    {
        $response = SnmpQuery::device($device)->get('SNMPv2-MIB::sysObjectID.0');

        $success = $response->getExitCode() === 0
            || $response->getExitCode() === 2
            || $response->isValid();

        return new ProbeResult($success, [
            'response' => $response,
        ]);
    }
}
