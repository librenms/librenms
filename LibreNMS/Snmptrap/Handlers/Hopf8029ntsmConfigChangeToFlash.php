<?php

/**
 * Hopf8029ntsmConfigChangeToFlash.php
 *
 * Handles the HOPF8029NTSM-MIB::ConfigChangeToFlash trap, sent when a
 * configuration change is saved persistently to flash storage.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class Hopf8029ntsmConfigChangeToFlash implements SnmptrapHandler
{
    public function handle(Device $device, Trap $trap)
    {
        $trap->log(
            'SNMP Trap: configuration change saved to flash storage on ' . ($device->display ?: $device->hostname ?: ''),
            Severity::Info,
            'reboot'
        );
    }
}
