<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class VeeamCdpRpoReport implements SnmptrapHandler
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
        $policy_name = $trap->getOidData('VEEAM-MIB::cdpPolicyName');
        $vm_name = $trap->getOidData('VEEAM-MIB::vmName');
        $result = $trap->getOidData('VEEAM-MIB::cdpRpoStatus');
        $color = ['Success' => 1, 'Warning' => 4, 'Failed' => 5];

        $trap->log('SNMP Trap: CDP policy RPO status change' . $result . ' - ' . $policy_name . ' ' . $vm_name, $color[$result], 'policy');
    }
}
