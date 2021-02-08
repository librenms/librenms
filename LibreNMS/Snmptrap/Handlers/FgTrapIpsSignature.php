<?php
/**
 * FgTrapIpsSignature.php
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
 * Traffic pattern matches Fortigate IPS signature.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class FgTrapIpsSignature implements SnmptrapHandler
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
        $srcIp = $trap->getOidData($trap->findOid('FORTINET-FORTIGATE-MIB::fgIpsTrapSrcIp'));
        $sigNum = $trap->getOidData($trap->findOid('FORTINET-FORTIGATE-MIB::fgIpsTrapSigId'));
        $sigName = $trap->getOidData($trap->findOid('FORTINET-FORTIGATE-MIB::fgIpsTrapSigMsg'));
        Log::event("IPS signature $sigName detected from $srcIp with Fortiguard ID $sigNum", $device->device_id, 'trap', 4);
    }
}
