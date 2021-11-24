<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class VeeamWebDownloadFinished implements SnmptrapHandler
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
        $color = ['Success' => 1, 'Warning' => 4, 'Failed' => 5];

        Log::event('SNMP Trap: 1 click FLR job ' . $result . ' - ' . $vm_name . ' - ' . $initiator_name, $device->device_id, 'backup', $color[$result]);
    }
}
