<?php

/**
 * Hopf8029ntsmUpdateInit.php
 *
 * Handles the HOPF8029NTSM-MIB::UpdateInit trap, sent when the user
 * initiates a firmware update. The transmitted values are the firmware
 * version, date, and serial number as they were *before* the update.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmUpdateInit implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $version = $trap->getOidData($trap->findOid('hopf8029NTSMFirmwareVersion'));
        $date = $trap->getOidData($trap->findOid('hopf8029NTSMFirmwareDate'));

        $trap->log(
            'SNMP Trap: firmware update initiated on ' . ($device->display ?: $device->hostname ?: '')
                . ' (previous version: ' . $version . ', dated ' . $date . ')',
            Severity::Warning,
            'reboot'
        );
    }
}
