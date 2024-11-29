<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class VeeamLinuxFLRCopyToFinished extends VeeamTrap implements SnmptrapHandler
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
        $target_host = $trap->getOidData('VEEAM-MIB::targetHost');
        $target_dir = $trap->getOidData('VEEAM-MIB::targetDir');
        $time = $trap->getOidData('VEEAM-MIB::transferTime');
        $result = $trap->getOidData('VEEAM-MIB::transferStatus');
        $severity = $this->getResultSeverity($result);

        $trap->log('SNMP Trap: FLR job ' . $result . ' - ' . $vm_name . ' - ' . $initiator_name . ' ' . $target_host . ' ' . $target_dir . ' Time taken: ' . $time, $severity, 'backup');
    }
}
