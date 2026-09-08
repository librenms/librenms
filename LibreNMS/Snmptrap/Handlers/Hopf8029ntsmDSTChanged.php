<?php

/**
 * Hopf8029ntsmDSTChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::DSTChanged trap, sent when the daylight
 * saving time indicator of the sync module changes.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmDSTChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $value = $trap->getOidData($trap->findOid('SyncModuleTimeDST'));
        $on = ((string) $value === '1' || (string) $value === 'On');

        $trap->log(
            'SNMP Trap: daylight saving time is now ' . ($on ? 'On' : 'Off'),
            Severity::Notice,
            'sensor'
        );
    }
}
