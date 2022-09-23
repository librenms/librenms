<?php

namespace LibreNMS\Interfaces\Polling\Netstats;

interface TcpNetstatsPolling
{
    public function pollTcpNetstats(array $oids): array;
}
