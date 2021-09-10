<?php

namespace LibreNMS\Interfaces\Polling;
use Illuminate\Support\Collection;

interface WirelessCountersPolling
{
    public function pollWirelessCounters();
}
