<?php

namespace LibreNMS\Polling\Method\Probe;

use App\Models\Device;
use SnmpQuery;

class SnmpProbe extends PollingMethodProbe
{
    public function check(Device $device): ProbeResult
    {
        $response = SnmpQuery::device($device)->get('SNMPv2-MIB::sysObjectID.0');

        $success = $response->getExitCode() === 0
            || $response->getExitCode() === 2
            || $response->isValid();

        $error = (! $success) ? ($response->getErrorMessage() ?: ($response->stderr ?: null)) : null;

        return new ProbeResult($success, [
            'response' => $response,
            'error' => $error,
        ], $error);
    }
}
