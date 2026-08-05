<?php

/**
 * Hopf8029ntsmNTPSystemPeerChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::NTPSystemPeerChanged trap, sent when the
 * system peer of the NTP service changes.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmNTPSystemPeerChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $refId = $trap->getOidData($trap->findOid('ntpSysRefId'));
        $stratum = $trap->getOidData($trap->findOid('ntpSysStratum'));

        $trap->log(
            'SNMP Trap: NTP system peer changed (refId: ' . $refId . ', stratum: ' . $stratum . ')',
            Severity::Notice,
            'sensor'
        );
    }
}
