<?php

namespace LibreNMS\Interfaces\Polling\Netstats;

interface SnmpNetstatsPolling
{
    public function pollSnmpNetstats(array $oids): array;
}
