<?php

/**
 * Hopf8029ntsmLeapAnnounceChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::LeapAnnounceChanged trap, sent when a leap
 * second insertion is announced or cleared (announcement is switched on
 * one hour before the actual insertion occurs).
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmLeapAnnounceChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $value = $trap->getOidData($trap->findOid('SyncModuleTimeLEAP'));
        $on = ((string) $value === '1' || (string) $value === 'On');

        $trap->log(
            'SNMP Trap: leap second announcement ' . ($on ? 'set (insertion within 1 hour)' : 'cleared'),
            $on ? Severity::Warning : Severity::Ok,
            'sensor'
        );
    }
}
