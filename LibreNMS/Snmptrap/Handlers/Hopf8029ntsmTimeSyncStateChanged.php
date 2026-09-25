<?php

/**
 * Hopf8029ntsmTimeSyncStateChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::TimeSyncStateChanged trap, sent when the
 * synchronisation status of the sync module changes.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmTimeSyncStateChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $state = $trap->getOidData($trap->findOid('SyncModuleTimeSyncState'));

        $severity = Severity::Notice;
        if ($state === 'I') {
            $severity = Severity::Error; // invalid time and date
        } elseif ($state === 'C' || $state === 'r') {
            $severity = Severity::Warning; // crystal mode or low precision radio sync
        } elseif ($state === 'R') {
            $severity = Severity::Ok; // radio synchronous, high precision
        }

        $trap->log(
            'SNMP Trap: sync module time sync state changed to ' . $state,
            $severity,
            'sensor'
        );
    }
}
