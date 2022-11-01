<?php
/**
 * OspfTxRetransmit.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Andy Norwood
 * @author     Andy Norwood <andytnorwood@gmail.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class OspfTxRetransmit implements SnmptrapHandler
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
        $ospfRouterId = $trap->getOidData($trap->findOid('OSPF-MIB::ospfRouterId'));
        $ospfPacketType = $trap->getOidData($trap->findOid('OSPF-TRAP-MIB::ospfPacketType'));
        $ospfLsdbRouterId = $trap->getOidData($trap->findOid('OSPF-MIB::ospfLsdbRouterId'));
        $ospfLsdbType = $trap->getOidData($trap->findOid('OSPF-MIB::ospfLsdbType'));
        $ospfNbrRtrId = $trap->getOidData($trap->findOid('OSPF-MIB::ospfNbrRtrId'));
        $ospfLsdbLsid = $trap->getOidData($trap->findOid('OSPF-MIB::ospfLsdbLsid'));

        if ($ospfPacketType != 'lsUpdate') {
            $trap->log('SNMP TRAP: ' . $device->displayName() . "(Router ID: $ospfRouterId) sent a $ospfPacketType packet to $ospfNbrRtrId.");

            return;
        }

        $trap->log('SNMP Trap: OSPFTxRetransmit trap received from ' . $device->displayName() . "(Router ID: $ospfRouterId). A $ospfPacketType packet was sent to $ospfNbrRtrId. LSType: $ospfLsdbType, route ID: $ospfLsdbLsid, originating from $ospfLsdbRouterId.");
    }
}
