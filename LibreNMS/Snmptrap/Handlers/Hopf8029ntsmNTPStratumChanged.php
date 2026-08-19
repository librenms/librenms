<?php

/**
 * Hopf8029ntsmNTPStratumChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::NTPStratumChanged trap, sent when the
 * stratum of the NTP service changes.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmNTPStratumChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $stratum = $trap->getOidData($trap->findOid('ntpSysStratum'));
        $refId = $trap->getOidData($trap->findOid('ntpSysRefId'));

        $trap->log(
            'SNMP Trap: NTP stratum changed to ' . $stratum . ' (refId: ' . $refId . ')',
            Severity::Notice,
            'sensor'
        );
    }
}
