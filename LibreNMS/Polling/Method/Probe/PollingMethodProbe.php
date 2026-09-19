<?php

namespace LibreNMS\Polling\Method\Probe;

use App\Models\Device;

abstract class PollingMethodProbe
{
    abstract public function check(Device $device): ProbeResult;
}
