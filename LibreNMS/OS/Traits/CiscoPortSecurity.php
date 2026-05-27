<?php

/**
 * CiscoCellular.php
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
 * @copyright  2023 Michael Adams
 * @author     Michael Adams <mradams@ilstu.edu>
 */

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\PortSecurity;
use Illuminate\Support\Collection;
use LibreNMS\OS;
use SnmpQuery;

trait CiscoPortSecurity
{
    public function pollPortSecurity(OS $os): Collection
    {
        return SnmpQuery::enumStrings()->walk([
            'CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityEnable',
            'CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityStatus',
            'CISCO-PORT-SECURITY-MIB::cpsIfMaxSecureMacAddr',
            'CISCO-PORT-SECURITY-MIB::cpsIfCurrentSecureMacAddrCount',
            'CISCO-PORT-SECURITY-MIB::cpsIfViolationAction',
            'CISCO-PORT-SECURITY-MIB::cpsIfViolationCount',
            'CISCO-PORT-SECURITY-MIB::cpsIfSecureLastMacAddress',
            'CISCO-PORT-SECURITY-MIB::cpsIfStickyEnable',
        ])->mapTable(function ($snmp, $ifIndex) use ($os) {
            if (! array_key_exists('CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityEnable', $snmp)) {
                return null;
            }

            $port_id = PortCache::getIdFromIfIndex($ifIndex);
            if ($port_id === null) {
                return null;
            }

            return new PortSecurity([
                'port_id' => $port_id,
                'device_id' => $os->getDeviceId(),
                'port_security_enable' => ($snmp['CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityEnable'] ?? null) === 'true',
                'status' => $snmp['CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityStatus'] ?? null,
                'max_addresses' => $snmp['CISCO-PORT-SECURITY-MIB::cpsIfMaxSecureMacAddr'] ?? null,
                'address_count' => $snmp['CISCO-PORT-SECURITY-MIB::cpsIfCurrentSecureMacAddrCount'] ?? null,
                'violation_action' => $snmp['CISCO-PORT-SECURITY-MIB::cpsIfViolationAction'] ?? null,
                'violation_count' => $snmp['CISCO-PORT-SECURITY-MIB::cpsIfViolationCount'] ?? null,
                'last_mac_address' => $snmp['CISCO-PORT-SECURITY-MIB::cpsIfSecureLastMacAddress'] ?? null,
                'sticky_enable' => isset($snmp['CISCO-PORT-SECURITY-MIB::cpsIfStickyEnable']) ? $snmp['CISCO-PORT-SECURITY-MIB::cpsIfStickyEnable'] === 'true' : null,
            ]);
        })->filter();
    }
}
