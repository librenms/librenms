<?php
/**
 * CiscoErrDisableInerfaceEvent.php
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
 * @author     Andy Norwood(bonzo81))
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;

class CiscoErrDisableInterfaceEvent implements SnmptrapHandler
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
        $ifIndex = explode('.', $trap->findOid('CISCO-ERR-DISABLE-MIB::cErrDisableIfStatusCause'));

        $port = $device->ports()->where('ifIndex', $ifIndex[1])->first();
        $cause = $trap->getOidData($trap->findOid('CISCO-ERR-DISABLE-MIB::cErrDisableIfStatusCause.' . $ifIndex[1] . '.0'));

        if (! $port) {
            $trap->log('SNMP TRAP: ' . $cause . ' error detected on unknown port. Either ifIndex is not found in the trap, or it does not match a port on this device.', Severity::Warning);

            return;
        }
        $trap->log('SNMP TRAP: ' . $cause . ' error detected on ' . $port->ifName . ' (Description: ' . $port->ifDescr . '). ' . $port->ifName . ' in err-disable state.', Severity::Warning);
    }
}
