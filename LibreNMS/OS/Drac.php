<?php

namespace LibreNMS\OS;

use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Drac extends OS implements ProcessorDiscovery, MempoolsDiscovery
{
    /**
     * iDRAC SNMP does not expose CPU utilisation percentage OIDs.
     * Return empty to prevent the HR/UCD MIB fallback from discovering
     * the iDRAC's own ARM processor as a spurious entry.
     */
    public function discoverProcessors(): array
    {
        return [];
    }

    /**
     * iDRAC SNMP does not expose OS-level memory utilisation OIDs.
     * Return empty to prevent the HR/UCD MIB fallback from discovering
     * the iDRAC's own management controller memory as a spurious entry.
     */
    public function discoverMempools(): Collection
    {
        return collect();
    }
}
