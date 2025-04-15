<?php

/**
 * RuckusSzApRadiusServerReachableTrap.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Ruckus RuckusSZAPRadiusServerReachableTrap occurs when the SmartZone receives
 * an event from a connected access point that it detects the RADIUS server as up from
 * down.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class RuckusSzApRadiusServerReachableTrap implements SnmptrapHandler
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
        $apName = $trap->getOidData($trap->findOid('RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPName'));
        $apIpv4 = $trap->getOidData($trap->findOid('RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIP'));
        $apIpv6 = $trap->getOidData($trap->findOid('RUCKUS-SZ-EVENT-MIB::ruckusSZEventAPIPv6'));
        $radiusIp = $trap->getOidData($trap->findOid('RUCKUS-SZ-EVENT-MIB::ruckusSZRadSrvrIp'));

        $message = "AP $apName ($apIpv4) is able to reach radius server $radiusIp";
        if (! empty($apIpv6)) {
            $message = "AP $apName ($apIpv4, $apIpv6) is able to reach radius server $radiusIp";
        }

        $trap->log("$message", Severity::Ok);
    }
}
