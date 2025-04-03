<?php

/**
 * ApcTrapUtil.php
 *
 * -Description-
 *
 * Common utility class for handling Ciena CES Notification Traps.
 * Traps from the CienaCESPortXcvr MIB carry the same set of OIDs.
 * This utility class cuts down on code reuse.
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
 * @copyright  2024 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use LibreNMS\Snmptrap\Trap;

class CienaCesPortNotificationUtils
{
    /**
     * Get Ciena Chassis ID
     *
     * @param  Trap  $trap
     * @return string
     */
    public static function getCienaChassis($trap)
    {
        if (str_starts_with($trap->getOidData($trap->findOid('SNMPv2-MIB::snmpTrapOID.0')), 'CIENA-CES-PORT-MIB')) {
            return $trap->getOidData($trap->findOid('CIENA-CES-PORT-MIB::cienaCesChPortPgIdMappingChassisIndex'));
        } else {
            return $trap->getOidData($trap->findOid('CIENA-CES-PORT-XCVR-MIB::cienaCesPortXcvrNotifChassisIndex'));
        }
    }

    /**
     * Get Ciena Shelf ID
     *
     * @param  Trap  $trap
     * @return string
     */
    public static function getCienaShelf($trap)
    {
        if (str_starts_with($trap->getOidData($trap->findOid('SNMPv2-MIB::snmpTrapOID.0')), 'CIENA-CES-PORT-MIB')) {
            return $trap->getOidData($trap->findOid('CIENA-CES-PORT-MIB::cienaCesPortPgIdMappingShelfIndex'));
        } else {
            return $trap->getOidData($trap->findOid('CIENA-CES-PORT-XCVR-MIB::cienaCesPortXcvrNotifShelfIndex'));
        }
    }

    /**
     * Get Ciena Slot ID
     *
     * @param  Trap  $trap
     * @return string
     */
    public static function getCienaSlot($trap)
    {
        if (str_starts_with($trap->getOidData($trap->findOid('SNMPv2-MIB::snmpTrapOID.0')), 'CIENA-CES-PORT-MIB')) {
            return $trap->getOidData($trap->findOid('CIENA-CES-PORT-MIB::cienaCesChPortPgIdMappingNotifSlotIndex'));
        } else {
            return $trap->getOidData($trap->findOid('CIENA-CES-PORT-XCVR-MIB::cienaCesPortXcvrNotifSlotIndex'));
        }
    }

    /**
     * Get Ciena Port ID
     *
     * @param  Trap  $trap
     * @return string
     */
    public static function getCienaPort($trap)
    {
        if (str_starts_with($trap->getOidData($trap->findOid('SNMPv2-MIB::snmpTrapOID.0')), 'CIENA-CES-PORT-MIB')) {
            return $trap->getOidData($trap->findOid('CIENA-CES-PORT-MIB::cienaCesPortPgIdMappingNotifPortNumber'));
        } else {
            return $trap->getOidData($trap->findOid('CIENA-CES-PORT-XCVR-MIB::cienaCesPortXcvrNotifPortNumber'));
        }
    }
}
