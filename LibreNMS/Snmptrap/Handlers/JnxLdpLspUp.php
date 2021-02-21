<?php
/**
 * JnxLdpLspUp.php
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
 * @copyright  2018 KanREN, Inc.
 * @author     Neil Kahle <nkahle@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class JnxLdpLspUp implements SnmptrapHandler
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
        $lspForward = $trap->getOidData($trap->findOid('JUNIPER-LDP-MIB::jnxLdpLspFec'));
        $routerID = $trap->getOidData($trap->findOid('JUNIPER-LDP-MIB::jnxLdpRtrid'));
        $instanceName = $trap->getOidData($trap->findOid('JUNIPER-LDP-MIB::jnxLdpInstanceName'));

        Log::event("LDP session $instanceName from $routerID to $lspForward is now up.", $device->device_id, 'trap', 1);
    }
}
