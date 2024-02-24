<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class VeeamBackupJobCompleted extends VeeamTrap implements SnmptrapHandler
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
        $name = $trap->getOidData('VEEAM-MIB::backupJobName');
        $comment = $trap->getOidData('VEEAM-MIB::backupJobComment');
        $result = $trap->getOidData('VEEAM-MIB::backupJobResult');
        $severity = $this->getResultSeverity($result);

        $trap->log('SNMP Trap: Backup Job ' . $result . ' - ' . $name . ' - ' . $comment, $severity, 'backup');
    }
}
