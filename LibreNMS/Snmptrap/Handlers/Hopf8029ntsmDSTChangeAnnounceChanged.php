<?php

/**
 * Hopf8029ntsmDSTChangeAnnounceChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::DSTChangeAnnounceChanged trap, sent when
 * the daylight saving time change announcement is set or cleared
 * (announcement is switched on one hour before the actual change).
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmDSTChangeAnnounceChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $value = $trap->getOidData($trap->findOid('SyncModuleTimeDSTChange'));
        $on = ((string) $value === '1' || (string) $value === 'On');

        $trap->log(
            'SNMP Trap: DST change announcement ' . ($on ? 'set (change within 1 hour)' : 'cleared'),
            $on ? Severity::Notice : Severity::Ok,
            'sensor'
        );
    }
}
