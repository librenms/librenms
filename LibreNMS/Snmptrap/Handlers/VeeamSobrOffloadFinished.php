<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class VeeamSobrOffloadFinished implements SnmptrapHandler
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
        $color = ['Success' => 1, 'Warning' => 4, 'Failed' => 5];

        Log::event('SNMP Trap: Scale-out offload job ' . $result . ' - ' . $name, $device->device_id, 'backup', $color[$result]);
    }
}
