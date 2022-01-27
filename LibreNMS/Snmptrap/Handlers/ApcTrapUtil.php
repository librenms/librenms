<?php
/**
 * ApcTrapUtil.php
 *
 * -Description-
 *
 * Common utility class for handling APC Power traps.
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
 * @copyright  2022 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */
 
namespace LibreNMS\Snmptrap\Handlers;

use LibreNMS\Snmptrap\Trap;

class ApcTrapUtil
{
    /**
     * Get the APC PDU Name
     *
     * @param  Trap  $trap
     * @return string
     */
    public static function getPduIdentName($trap)
    {
        return $trap->getOidData($trap->findOid('PowerNet-MIB::rPDUIdentName'));
    }

    /**
     * Get the APC PDU Phase Number
     *
     * @param  Trap  $trap
     * @return string
     */
    public static function getPduPhaseNum($trap)
    {
        return $trap->getOidData($trap->findOid('PowerNet-MIB::rPDULoadStatusPhaseNumber'));
    }

    /**
     * Get the APC Trap String
     *
     * @param  Trap  $trap
     * @return string
     */
    public static function getApcTrapString($trap)
    {
        return $trap->getOidData($trap->findOid('PowerNet-MIB::mtrapargsString'));
    }
}