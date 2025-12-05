<?php

/**
 * PortsVoiceVlan.php
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
 * @copyright  2025 Michael Adams
 * @author     Michael Adams <mradams@ilstu.edu>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Port;
use App\Models\PortsVoiceVlan;
use Illuminate\Support\Collection;

trait PortsVoiceVlan
{
    public function pollPortsVoiceVlan($os, $device): Collection
    {
        // Polling for current data
        $port_id = 0;
        $record = [];
        $device = $device->toArray();

        $port_voice_vlan_snmp = [];
        $port_voice_vlan_snmp = \SnmpQuery::hideMib()->enumStrings()->walk([
            'CISCO-VLAN-MEMBERSHIP-MIB::vmVoiceVlanId',
        ])->table(1);

        // Storing all polled data into an array using ifIndex as the index
        // Getting all ports from device. Port has to exist in ports table to be populated in ports_voice_vlan
        // Using ifIndex to map the voice_vlan data to a port_id to compare/update against the correct records
        $ports = new Port();
        $device = $os->getDevice();
        $device_id = $device->device_id;
        $port_list = $ports->select('port_id', 'ifIndex')->where('device_id', $device_id)->get()->toArray();
        $port_key = [];
        foreach ($port_list as $item) {
            $if_index = $item['ifIndex'];
            $port_id = $item['port_id'];
            $port_key[$if_index] = $port_id;
            $port_voice_vlan_snmp[$if_index]['ifIndex'] = $if_index;

            if (array_key_exists($if_index, $port_voice_vlan_snmp)) {
                $port_voice_vlan_snmp[$if_index]['port_id'] = $port_id;
                $port_voice_vlan_snmp[$if_index]['device_id'] = $device_id;
            }
        }

        // Assigning port_id and device_id to SNMP array for comparison
        $port_voice_vlan = $device->portSecurity;

        foreach ($port_voice_vlan_snmp as $item) {
            $if_index = $item['ifIndex'];
            if (array_key_exists('ifIndex', $port_voice_vlan_snmp[$if_index]) and array_key_exists($port_voice_vlan_snmp[$if_index]['ifIndex'], $port_key)) {
                $port_voice_vlan_snmp[$if_index]['port_id'] = $port_key[$port_voice_vlan_snmp[$if_index]['ifIndex']];
                $port_voice_vlan_snmp[$if_index]['device_id'] = $device_id;
            }

            if (array_key_exists($if_index, $port_key)) {
                $port_id = $port_key[$if_index];

                // Map SNMP fields to database schema fields
                $record = [];
                $record['port_id'] = $port_voice_vlan_snmp[$if_index]['port_id'] ?? null;
                $record['device_id'] = $port_voice_vlan_snmp[$if_index]['device_id'] ?? null;
                $record['voice_vlan'] = $port_voice_vlan_snmp[$if_index]['vmVoiceVlanId'] ?? null;
            }

            $update = new PortsVoiceVlan;
            $entry = $port_voice_vlan->where('port_id', $port_id)->first();
            // Checking if entry exists in DB already. If true, compare polled to DB.
            // If no entry, insert new.
            if ($entry) {
                $entry = $entry->toArray();
                unset($entry['id']);
                unset($entry['laravel_through_key']);

                // Remove null values from record for comparison
                $record = array_filter($record, fn ($value) => $value !== null);

                // Checking that polled data exists and doesn't match. Else if polled data exists, insert a new record.
                if (isset($record['voice_vlan']) and $record != $entry) {
                    unset($record['port_id']);
                    //echo "Updating\n";
                    $update->where('port_id', $port_id)->update($record);
                }
            } elseif (isset($record['voice_vlan'])) {
                //$update->where('port_id', $port_id)->update($record);
                //echo "Creating\n";
                $update->create($record);
            }
        }

        return $port_voice_vlan;
    }
}
