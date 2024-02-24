<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class VeeamVmBackupCompleted extends VeeamTrap implements SnmptrapHandler
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
        $job_name = $trap->getOidData('VEEAM-MIB::backupJobName');
        $vm_name = $trap->getOidData('VEEAM-MIB::vmName');
        $comment = $trap->getOidData('VEEAM-MIB::vmBackupComment');
        $result = $trap->getOidData('VEEAM-MIB::vmBackupResult');
        $severity = $this->getResultSeverity($result);

        $trap->log('SNMP Trap: VM backup ' . $result . ' - ' . $vm_name . ' Job: ' . $job_name . ' - ' . $comment, $severity, 'backup');
    }
}
