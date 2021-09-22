<?php
/**
 * OccamEpsPathGroupHeartbeatNotification.php
 *
 * Handles the OCCAM-EPS-MIB::epsPathGroupHeartbeatNotification trap
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Vantage Point Solutions
 * @author     Eric Graham <eric.graham@vantagepnt.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class OccamEpsPathGroupHeartbeatNotification implements SnmptrapHandler
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
        $shelfIndex = "unknown";
        $slotIndex = "unknown";
        $epsRingIndex = "unknown";
        $epsPathGroupIndex = "unknown";
        $epsHeartbeatStatus = "unknown";

        if ($trap_oid = $trap->findOid('OCCAM-SHELF-MIB::cardShelfIndex'))
            $shelfIndex = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-SHELF-MIB::cardSlotIndex'))
            $slotIndex = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-EPS-MIB::epsRingIndex'))
            $epsRingIndex = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-EPS-MIB::epsPathGroupIndex'))
            $epsPathGroupIndex = $trap->getOidData($trap_oid);

        if ($trap_oid = $trap->findOid('OCCAM-EPS-MIB::epsHeartbeatStatus')) {
            $heartbeatStatusRaw = $trap->getOidData($trap_oid);

            if ($heartbeatStatusRaw == 0)
                $epsHeartbeatStatus = "down";
            else if ($heartbeatStatusRaw == 1)
                $epsHeartbeatStatus = "up";
        }

        Log::event("EPS PG Heartbeat Notification: PG status $epsHeartbeatStatus on PG index $epsPathGroupIndex ring $epsRingIndex of shelf $shelfIndex/$slotIndex", $device->device_id, 'trap', 4);
    }
}
