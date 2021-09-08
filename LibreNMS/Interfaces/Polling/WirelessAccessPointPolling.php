<?php

namespace LibreNMS\Interfaces\Polling;
use Illuminate\Support\Collection;

interface WirelessAccessPointPolling
{
    public function pollWirelessAccessPoints();
}
