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

        foreach ($portsec_snmp as $item) {
            $if_index = $item['ifIndex'];
            if (array_key_exists('ifIndex', $portsec_snmp[$if_index]) and array_key_exists($portsec_snmp[$if_index]['ifIndex'], $port_key)) {
                $portsec_snmp[$if_index]['port_id'] = $port_key[$portsec_snmp[$if_index]['ifIndex']];
                $portsec_snmp[$if_index]['device_id'] = $device_id;
            }

            if (array_key_exists($if_index, $port_key)) {
                $port_id = $port_key[$if_index];

                // Map SNMP fields to database schema fields
                $record = [];
                $record['port_id'] = $portsec_snmp[$if_index]['port_id'] ?? null;
                $record['device_id'] = $portsec_snmp[$if_index]['device_id'] ?? null;
                $record['port_security_enable'] = $portsec_snmp[$if_index]['cpsIfPortSecurityEnable'] ?? null;
                $record['status'] = $portsec_snmp[$if_index]['cpsIfPortSecurityStatus'] ?? null;
                $record['max_addresses'] = $portsec_snmp[$if_index]['cpsIfMaxSecureMacAddr'] ?? null;
                $record['address_count'] = $portsec_snmp[$if_index]['cpsIfCurrentSecureMacAddrCount'] ?? null;
                $record['violation_action'] = $portsec_snmp[$if_index]['cpsIfViolationAction'] ?? null;
                $record['violation_count'] = $portsec_snmp[$if_index]['cpsIfViolationCount'] ?? null;
                $record['last_mac_address'] = $portsec_snmp[$if_index]['cpsIfSecureLastMacAddress'] ?? null;
                $record['sticky_enable'] = $portsec_snmp[$if_index]['cpsIfStickyEnable'] ?? null;
            }

            $update = new PortSecurity;
            $entry = $portsec->where('port_id', $port_id)->first();
            // Checking if entry exists in DB already. If true, compare polled to DB.
            // If no entry, insert new.
            if ($entry) {
                $entry = $entry->toArray();
                unset($entry['id']);
                unset($entry['laravel_through_key']);

                // Remove null values from record for comparison
                $record = array_filter($record, function ($value) {
                    return $value !== null;
                });

                // Checking that polled data exists and doesn't match. Else if polled data exists, insert a new record.
                if (isset($record['port_security_enable']) and $record != $entry) {
                    unset($record['port_id']);
                    //echo "Updating\n";
                    $update->where('port_id', $port_id)->update($record);
                }
            } elseif (isset($record['port_security_enable'])) {
                //$update->where('port_id', $port_id)->update($record);
                //echo "Creating\n";
                $update->create($record);
            }
        }

        return $portsec;
    }
}
