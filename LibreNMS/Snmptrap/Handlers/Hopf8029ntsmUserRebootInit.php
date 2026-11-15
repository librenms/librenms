<?php

/**
 * Hopf8029ntsmUserRebootInit.php
 *
 * Handles the HOPF8029NTSM-MIB::UserRebootInit trap, sent when the user
 * initiates a reboot of the 8029NTS/M time server.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmUserRebootInit implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $trap->log(
            'SNMP Trap: user initiated a reboot of ' . ($device->display ?: $device->hostname ?: ''),
            Severity::Warning,
            'reboot'
        );
    }
}
