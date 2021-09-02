<?php
/**
 * OspfIfStateChange.php
 *
 * -Description-
 * Handles the ospfIfStateChange SNMP trap signaling an interface
 * in the OSPF topology has changed its state. The handler logs the
 * change and updates the interface's state in ospf_ports table.
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
 * @copyright  2020 KanREN Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class OspfIfStateChange implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $ospfIfIpAddress = $trap->getOidData($trap->findOid('OSPF-MIB::ospfIfIpAddress'));
        $ospfPort = $device->ospfPorts()->where('ospfIfIpAddress', $ospfIfIpAddress)->first();

        $port = $device->ports()->where('port_id', $ospfPort->port_id)->first();

        if (! $port) {
            Log::warning("Snmptrap ospfIfStateChange: Could not find port at port_id $ospfPort->port_id for device: " . $device->hostname);

            return;
        }

        $ospfPort->ospfIfState = $trap->getOidData($trap->findOid('OSPF-MIB::ospfIfState'));

        switch ($ospfPort->ospfIfState) {
            case 'down':
                $severity = 5;
                break;
            case 'designatedRouter':
                $severity = 1;
                break;
            case 'backupDesignatedRouter':
                $severity = 1;
                break;
            case 'otherDesignatedRouter':
                $severity = 1;
                break;
            case 'pointToPoint':
                $severity = 1;
                break;
            case 'waiting':
                $severity = 4;
                break;
            case 'loopback':
                $severity = 4;
                break;
            default:
                $severity = 0;
                break;
        }

        Log::event("OSPF interface $port->ifName is $ospfPort->ospfIfState", $device->device_id, 'trap', $severity);

        $ospfPort->save();
    }
}
