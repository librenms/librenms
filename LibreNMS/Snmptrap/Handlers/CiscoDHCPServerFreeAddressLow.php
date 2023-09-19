<?php
/**
 * CiscoDHCPServerFreeAddressLow.php
 *
 * Logs an event when the number of available IP addresses for a DHCP
 * address pool has fallen below the defined low threshold. Configurable
 * by issuing the `utilization mark high <percent>` on the DHCP pool in
 * configuration mode.
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
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Josh Silvas
 * @author     Josh Silvas <josh@jsilvas.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class CiscoDHCPServerFreeAddressLow implements SnmptrapHandler
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
        $oid_prefix = 'CISCO-IETF-DHCP-SERVER-MIB::cDhcpv4ServerSharedNetFreeAddresses.';
        $oid = $trap->findOid($oid_prefix);
        $pool = str_replace($oid_prefix, '', $oid);
        $value = $trap->getOidData($oid);
        $trap->log("SNMP Trap: DHCP pool $pool address space low. Free addresses: '$value' addresses.", Severity::Error);
    }
}
