<?php
/**
 * CyberPowerUtil.php
 *
 * -Description-
 *
 * CyberPower UPS SNMP Trap utility class
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
 * @copyright  2020 KanREN Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use LibreNMS\Snmptrap\Trap;

class CyberPowerUtil
{
    /**
     * Get the trap message
     *
     * @param Trap $trap
     * @return string
     */
    public static function getMessage($trap)
    {
        return $trap->getOidData($trap->findOid('CPS-MIB::mtrapinfoString'));
    }
}
