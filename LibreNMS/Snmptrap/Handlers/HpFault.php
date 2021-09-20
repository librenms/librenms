<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class HpFault implements SnmptrapHandler
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
        $type = $trap->getOidData($trap->findOid('HP-ICF-FAULT-FINDER-MIB::hpicfFfLogFaultType'));
        switch ($type) {
    case 'badXcvr':
        Log::event('Fault - CRC Error ' . $trap->getOidData($trap->findOid('HP-ICF-FAULT-FINDER-MIB::hpicfFfFaultInfoURL')), $device->device_id, $type, 4);
        break;
    case 'badCable':
        Log::event('Fault - Bad Cable ' . $trap->getOidData($trap->findOid('HP-ICF-FAULT-FINDER-MIB::hpicfFfFaultInfoURL')), $device->device_id, $type, 4);
        break;
    case 'bcastStorm':
        Log::event('Fault - Broadcaststorm ' . $trap->getOidData($trap->findOid('HP-ICF-FAULT-FINDER-MIB::hpicfFfFaultInfoURL')), $device->device_id, $type, 5);
        break;
    default:
        Log::event('Fault - Unhandled ' . $trap->getOidData($trap->findOid('HP-ICF-FAULT-FINDER-MIB::hpicfFfFaultInfoURL')), $device->device_id, $type, 2);
        break;
    }
    }
}
