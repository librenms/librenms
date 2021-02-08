<?php
/**
 * LinkUp.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class LinkUp implements SnmptrapHandler
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
        $ifIndex = $trap->getOidData($trap->findOid('IF-MIB::ifIndex'));

        $port = $device->ports()->where('ifIndex', $ifIndex)->first();

        if (! $port) {
            Log::warning("Snmptrap linkUp: Could not find port at ifIndex $ifIndex for device: " . $device->hostname);

            return;
        }

        $port->ifOperStatus = $trap->getOidData("IF-MIB::ifAdminStatus.$ifIndex");
        $port->ifAdminStatus = $trap->getOidData("IF-MIB::ifOperStatus.$ifIndex");

        Log::event("SNMP Trap: linkUp $port->ifAdminStatus/$port->ifOperStatus " . $port->ifDescr, $device->device_id, 'interface', 1, $port->port_id);

        if ($port->isDirty('ifAdminStatus')) {
            Log::event("Interface Enabled : $port->ifDescr (TRAP)", $device->device_id, 'interface', 3, $port->port_id);
        }

        if ($port->isDirty('ifOperStatus')) {
            Log::event("Interface went Up : $port->ifDescr (TRAP)", $device->device_id, 'interface', 1, $port->port_id);
        }

        $port->save();
    }
}
