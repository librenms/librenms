<?php

namespace LibreNMS\Interfaces;

use App\Models\Device;
use LibreNMS\Polling\Method\Probe\ProbeResult;

interface PollingMethodProbeInterface
{
    public function check(Device $device): ProbeResult;
}
