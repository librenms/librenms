<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class VeeamSobrOffloadFinished extends VeeamTrap implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $name = $trap->getOidData('VEEAM-MIB::repositoryName');
        $result = $trap->getOidData('VEEAM-MIB::repositoryStatus');
        $severity = $this->getResultSeverity($result);

        $trap->log('SNMP Trap: Scale-out offload job ' . $result . ' - ' . $name, $severity, 'backup');
    }
}
