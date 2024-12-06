<?php

namespace LibreNMS\Interfaces\Polling\Netstats;

interface IpForwardNetstatsPolling
{
    public function pollIpForwardNetstats(array $oids): array;
}
