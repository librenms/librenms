<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class VeeamBackupJobCompleted implements SnmptrapHandler
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
        $color = ['Success' => 1, 'Warning' => 4, 'Failed' => 5];

        Log::event('SNMP Trap: Backup Job ' . $result . ' - ' . $name . ' - ' . $comment, $device->device_id, 'backup', $color[$result]);
    }
}
