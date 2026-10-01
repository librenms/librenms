<?php

/**
 * Hopf8029ntsmNTPAccuracyChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::NTPAccuracyChanged trap, sent when the
 * calculated value for NTP accuracy changes.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmNTPAccuracyChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $accuracy = $trap->getOidData($trap->findOid('ntpAccuracy'));

        $severity = Severity::Notice;
        if ((string) $accuracy === '0' || (string) $accuracy === 'low') {
            $severity = Severity::Warning;
        }

        $trap->log(
            'SNMP Trap: NTP accuracy changed to ' . $accuracy,
            $severity,
            'sensor'
        );
    }
}
