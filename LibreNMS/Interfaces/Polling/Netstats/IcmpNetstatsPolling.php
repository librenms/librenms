<?php

namespace LibreNMS\Interfaces\Polling\Netstats;

interface IcmpNetstatsPolling
{
    public function pollIcmpNetstats(array $oids): array;
}
