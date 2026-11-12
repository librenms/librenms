<?php

/**
 * FgTrapDhcp.php
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
 * Fortigate DHCP server event: pool exhausted, IP conflict, or NAK received.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class FgTrapDhcp implements SnmptrapHandler
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
        $type = $trap->getOidData($trap->findOid('FORTINET-FORTIGATE-MIB::fgDhcpTrapType'));
        $message = $trap->getOidData($trap->findOid('FORTINET-FORTIGATE-MIB::fgDhcpTrapMessage'));
        $ifName = $trap->getOidData($trap->findOid('IF-MIB::ifName')) ?: 'unknown interface';

        // snmptrapd may render the enum as "conflictIP", "conflictIP(2)" or "2"
        [$event, $severity] = match (preg_replace('/\D/', '', $type) ?: $type) {
            '1', 'runOutOfIPPool' => ['DHCP pool exhausted', Severity::Error],
            '2', 'conflictIP' => ['DHCP IP conflict', Severity::Warning],
            '3', 'receivedNAK' => ['DHCP NAK received', Severity::Notice],
            default => ['DHCP event', Severity::Warning],
        };

        $trap->log("$event on $ifName: $message", $severity);
    }
}
