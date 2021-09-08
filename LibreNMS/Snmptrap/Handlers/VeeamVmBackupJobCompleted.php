<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class VeeamVmBackupJobCompleted implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param  Device $device
     * @param  Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $name = $trap->getOidData('VEEAM-MIB::backupJobName');
        $comment = $trap->getOidData('VEEAM-MIB::vmName');

        if ($trap->getOidData('VEEAM-MIB::vmBackupResult') == 'Success') {
            Log::event('SNMP Trap: VM Backup success - ' . $name . '' . $comment, $device->device_id, 'backup', 1);
        } else {
            Log::event('SNMP Trap: VM Backup failed - ' . $name . ' ' . $comment, $device->device_id, 'backup', 5);
        }
    }
}
