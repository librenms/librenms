<?php
/**
 * HpicfBridgeLoopProtectLoopDetectedNotification.php
 *
 * -Description-
 *
 * A hpicfBridgeLoopProtectLoopDetectedNotification trap signifies that
 * a loop is detected by the loop protection protocol of the switch.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class HpicfBridgeLoopProtectLoopDetectedNotification implements SnmptrapHandler
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
        $ifIndex = $trap->getOidData($trap->findOid('IF-MIB::ifIndex'));

        $port = $device->ports()->where('ifIndex', $ifIndex)->first();

        $interface = $ifIndex . ' (ifIndex)';
        if ($port) {
            $interface = $port->ifDescr;
        } else {
            Log::warning("SnmpTrap HpicfBridgeLoopProtectLoopDetectedNotification: Could not find port at ifIndex $ifIndex for device: " . $device->hostname);
        }

        $trap->log('Loop Detected ' . $interface . ' (Count ' . $trap->getOidData($trap->findOid('HP-ICF-BRIDGE::hpicfBridgeLoopProtectPortLoopCount')) . ', Action ' . $trap->getOidData($trap->findOid('HP-ICF-BRIDGE::hpicfBridgeLoopProtectPortReceiverAction')) . ')', Severity::Warning, 'loop', $interface);
    }
}
