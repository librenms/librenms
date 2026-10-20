<?php

/**
 * Hopf8029ntsmHardwareErrorChanged.php
 *
 * Handles the HOPF8029NTSM-MIB::HardwareErrorChanged trap, sent when a
 * hardware error status changes on the sync module (crystal regulation,
 * FRAM, or sync channel errors).
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmHardwareErrorChanged implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $crystal = $trap->getOidData($trap->findOid('SyncModuleErrorCrystalRegulation'));
        $fram = $trap->getOidData($trap->findOid('SyncModuleErrorFRAM'));
        $syncChannel = $trap->getOidData($trap->findOid('SyncModuleErrorSyncChannel'));

        $anyError = ((string) $crystal === '1') || ((string) $fram === '1') || ((string) $syncChannel === '1');

        $trap->log(
            'SNMP Trap: hardware error status changed on ' . ($device->display ?: $device->hostname ?: '')
                . ' (crystal: ' . $crystal . ', FRAM: ' . $fram . ', syncChannel: ' . $syncChannel . ')',
            $anyError ? Severity::Error : Severity::Ok,
            'sensor'
        );
    }
}
