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

use App\Models\Port;
use App\Models\PortSecurity;
use Illuminate\Support\Collection;

trait CiscoPortSecurity
{
    public function pollPortSecurity($os, $device): Collection
    {
        // Polling for current data
        $port_id = 0;
        $record = [];
        $device = $device->toArray();

        $portsec_snmp = [];
        $portsec_snmp = \SnmpQuery::hideMib()->enumStrings()->walk([
            'CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityEnable',
            'CISCO-PORT-SECURITY-MIB::cpsIfPortSecurityStatus',
            'CISCO-PORT-SECURITY-MIB::cpsIfMaxSecureMacAddr',
            'CISCO-PORT-SECURITY-MIB::cpsIfCurrentSecureMacAddrCount',
            'CISCO-PORT-SECURITY-MIB::cpsIfViolationAction',
            'CISCO-PORT-SECURITY-MIB::cpsIfViolationCount',
            'CISCO-PORT-SECURITY-MIB::cpsIfSecureLastMacAddress',
            'CISCO-PORT-SECURITY-MIB::cpsIfStickyEnable',
        ])->table(1);

        // Storing all polled data into an array using ifIndex as the index
        // Getting all ports from device. Port has to exist in ports table to be populated in port_security
        // Using ifIndex to map the port-security data to a port_id to compare/update against the correct records
        $ports = new Port();
        $device = $os->getDevice();
        $device_id = $device->device_id;
        $port_list = $ports->select('port_id', 'ifIndex')->where('device_id', $device_id)->get()->toArray();
        $port_key = [];
        foreach ($port_list as $item) {
            $if_index = $item['ifIndex'];
            $port_id = $item['port_id'];
            $port_key[$if_index] = $port_id;
            $portsec_snmp[$if_index]['ifIndex'] = $if_index;

            if (array_key_exists($if_index, $portsec_snmp)) {
                $portsec_snmp[$if_index]['port_id'] = $port_id;
                $portsec_snmp[$if_index]['device_id'] = $device_id;
            }
        }

        // Assigning port_id and device_id to SNMP array for comparison
        $portsec = $device->portSecurity;

        foreach ($portsec_snmp as $if_index => $snmp) {
            if (! is_array($snmp) || ! array_key_exists($if_index, $port_key)) {
                continue;
            }

            if (! array_key_exists('cpsIfPortSecurityEnable', $snmp)) {
                continue;
            }

            $port_id = $port_key[$if_index];

            $record = [
                'port_id' => $port_id,
                'device_id' => $device_id,
                'port_security_enable' => ($snmp['cpsIfPortSecurityEnable'] ?? null) === 'true',
                'status' => $snmp['cpsIfPortSecurityStatus'] ?? null,
                'max_addresses' => $snmp['cpsIfMaxSecureMacAddr'] ?? null,
                'address_count' => $snmp['cpsIfCurrentSecureMacAddrCount'] ?? null,
                'violation_action' => $snmp['cpsIfViolationAction'] ?? null,
                'violation_count' => $snmp['cpsIfViolationCount'] ?? null,
                'last_mac_address' => $snmp['cpsIfSecureLastMacAddress'] ?? null,
                'sticky_enable' => array_key_exists('cpsIfStickyEnable', $snmp) ? $snmp['cpsIfStickyEnable'] === 'true' : null,
            ];

            $attributes = array_filter($record, fn ($value) => $value !== null);

            $entry = $portsec->where('port_id', $port_id)->first();

            if ($entry) {
                $entry->fill($attributes);

                if ($entry->isDirty()) {
                    $entry->save();
                }
            } else {
                PortSecurity::create($attributes);
            }
        }

        return $portsec;
    }
}
