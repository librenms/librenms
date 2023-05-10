<?php

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class SophosNotification implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $name = $trap->getOidData('SFOS-FIREWALL-MIB::sfosDeviceName.0');
        $comment = $trap->getOidData('SFOS-FIREWALL-MIB::sfosTrapMessage.0');

        $trap->log('Sophos Firewall notification - Device: ' . $name . ' - Message: ' . $comment, 3, 'Notification');
    }
}
