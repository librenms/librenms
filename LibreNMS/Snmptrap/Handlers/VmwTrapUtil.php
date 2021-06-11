<?php
/**
 * VmwTrapUtil.php
 *
 * -Description-
 *
 * Common utility class for handling VmWare ESXi traps.
 *
 * Assuming VMWare Tools is installed the VMHost will receive a periodic
 * heartbeat from a VMGuest. This trap is sent once a heartbeat is
 * received after not receiving heartbeats for a configured period
 * of time.
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
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use LibreNMS\Snmptrap\Trap;

class VmwTrapUtil
{
    /**
     * Get the VMGuest hostname
     *
     * @param Trap $trap
     * @return string
     */
    public static function getGuestName($trap)
    {
        return $trap->getOidData($trap->findOid('VMWARE-VMINFO-MIB::vmwVmDisplayName'));
    }

    /**
     * Get the VMGuest ID number
     *
     * @param Trap $trap
     * @return string
     */
    public static function getGuestId($trap)
    {
        return $trap->getOidData($trap->findOid('VMWARE-VMINFO-MIB::vmwVmID'));
    }

    /**
     * Get the VMGuest configuration path
     *
     * @param Trap $trap
     * @return string
     */
    public static function getGuestConfigPath($trap)
    {
        return $trap->getOidData($trap->findOid('VMWARE-VMINFO-MIB::vmwVmConfigFilePath'));
    }
}
