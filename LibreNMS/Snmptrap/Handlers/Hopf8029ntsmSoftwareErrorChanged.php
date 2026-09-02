<?php

/**
 * Hopf8029ntsmSoftwareErrorChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::SoftwareErrorChanged trap, sent when a
 * software/config error status changes on the sync module (PCID, time
 * zone, DST, or sync protocol errors).
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmSoftwareErrorChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $pcid = $trap->getOidData($trap->findOid('SyncModuleErrorPCID'));
        $timeZone = $trap->getOidData($trap->findOid('SyncModuleErrorTimeZone'));
        $dst = $trap->getOidData($trap->findOid('SyncModuleErrorDST'));
        $syncProtocol = $trap->getOidData($trap->findOid('SyncModuleErrorSyncProtocol'));

        $anyError = ((string) $pcid === '1') || ((string) $timeZone === '1')
            || ((string) $dst === '1') || ((string) $syncProtocol === '1');

        $trap->log(
            'SNMP Trap: software error status changed on ' . ($device->display ?: $device->hostname ?: '')
                . ' (PCID: ' . $pcid . ', timeZone: ' . $timeZone
                . ', DST: ' . $dst . ', syncProtocol: ' . $syncProtocol . ')',
            $anyError ? Severity::Warning : Severity::Ok,
            'sensor'
        );
    }
}
