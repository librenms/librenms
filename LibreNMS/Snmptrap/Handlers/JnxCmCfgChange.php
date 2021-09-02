<?php
/**
 * jnxCmCfgChange.php
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
 * Juniper configuration change trap. Includes interface used to affect
 * the change, the user, and the system time when the change was made.
 * If a commit confirmed is rolled back the source is "other" and the
 * user is "root".
 *
 * @link       https://www.librenms.org
 * @copyright  2018 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class JnxCmCfgChange implements SnmptrapHandler
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
        $source = $trap->getOidData($trap->findOid('JUNIPER-CFGMGMT-MIB::jnxCmCfgChgEventSource'));
        $user = $trap->getOidData($trap->findOid('JUNIPER-CFGMGMT-MIB::jnxCmCfgChgEventUser'));
        $changeTime = $trap->getOidData($trap->findOid('JUNIPER-CFGMGMT-MIB::jnxCmCfgChgEventDate'));
        if ($source == 'other' && $user == 'root') {
            Log::event("Config rolled back at $changeTime", $device->device_id, 'trap', 2);
        } else {
            Log::event("Config modified by $user from $source at $changeTime", $device->device_id, 'trap', 2);
        }
    }
}
