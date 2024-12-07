<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class VeeamWebDownloadFinished extends VeeamTrap implements SnmptrapHandler
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
        $initiator_name = $trap->getOidData('VEEAM-MIB::initiatorName');
        $vm_name = $trap->getOidData('VEEAM-MIB::vmName');
        $result = $trap->getOidData('VEEAM-MIB::transferStatus');
        $severity = $this->getResultSeverity($result);

        $trap->log('SNMP Trap: 1 click FLR job ' . $result . ' - ' . $vm_name . ' - ' . $initiator_name, $severity, 'backup');
    }
}
