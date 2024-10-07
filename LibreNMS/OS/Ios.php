<?php

/**
 * Ios.php
 *
 * Cisco IOS
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

<<<<<<< HEAD
use App\Models\Device;
use App\Models\Port;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
=======
>>>>>>> 4cbc88a538 (Cisco os discovery cleanups (#17868))
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessCellDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessChannelDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSnrDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\PortSecurityPolling;
use LibreNMS\OS\Shared\Cisco;
use LibreNMS\OS\Traits\CiscoCellular;
use LibreNMS\Interfaces\Polling\PortSecurityPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use Illuminate\Support\Collection;
use SnmpQuery;
use LibreNMS\OS;
use App\Models\Device;
use App\Observers\ModuleModelObserver;

class Ios extends Cisco implements
    WirelessCellDiscovery,
    WirelessChannelDiscovery,
    WirelessClientsDiscovery,
    WirelessRssiDiscovery,
    WirelessRsrqDiscovery,
    WirelessRsrpDiscovery,
    WirelessSnrDiscovery,
    PortSecurityPolling,
    OSPolling
{
    use CiscoCellular;

    public function pollOS(DataStorageInterface $datastore): void
    {
<<<<<<< HEAD
        // Don't poll Ciscowlc FIXME remove when wireless-controller module exists
=======
       	// Don't poll Ciscowlc FIXME remove when wireless-controller module exists
>>>>>>> 4cc4269ddd (Adding inheritance and interface, and optimizing polling.)
    }

    /**
     * @return WirelessSensor[] Sensors
     */
    public function discoverWirelessClients(): array
    {
        $device = $this->getDevice();

        if (empty($device->hardware) || (! str_starts_with($device->hardware, 'AIR-') && ! str_contains($device->hardware, 'ciscoAIR'))) {
            // unsupported IOS hardware
            return [];
        }

        $data = \SnmpQuery::walk('CISCO-DOT11-ASSOCIATION-MIB::cDot11ActiveWirelessClients')->table(1);

        if (empty($data)) {
            return [];
        }

        $this->mapToEntPhysical($data);

        $sensors = [];
        foreach ($data as $ifIndex => $entry) {
            $sensors[] = new WirelessSensor(
                'clients',
                $device['device_id'],
                ".1.3.6.1.4.1.9.9.273.1.1.2.1.1.$ifIndex",
                'ios',
                $ifIndex,
                $entry['entPhysicalDescr'],
                $entry['cDot11ActiveWirelessClients'],
                1,
                1,
                'sum',
                null,
                40,
                null,
                30,
                $entry['entPhysicalIndex'],
                'ports'
            );
        }

        return $sensors;
    }
<<<<<<< HEAD

<<<<<<< HEAD
    public function pollPortSecurity($os, $device): Collection
    {
        // Polling for current data
        $port_id = 0;
        $record = [];
        $device = $device->toArray();

        $portsec_snmp = [];
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfPortSecurityEnable', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfPortSecurityStatus', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfMaxSecureMacAddr', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfCurrentSecureMacAddrCount', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfViolationAction', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfViolationCount', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfSecureLastMacAddress', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfStickyEnable', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');
        $portsec_snmp = snmpwalk_cache_oid($device, 'cpsIfSecureLastMacAddrVlanId', $portsec_snmp, 'CISCO-PORT-SECURITY-MIB');

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
                $record = $portsec_snmp[$if_index];
                unset($record['ifIndex']);
            }

            $update = new \App\Models\PortSecurity;
            $entry = $portsec->where('port_id', $port_id)->first();
            // Checking if entry exists in DB already. If true, compare polled to DB.
            // If no entry, insert new.
            if ($entry) {
                $entry = $entry->toArray();
                unset($entry['id']);
                unset($entry['laravel_through_key']);
                // This OID currently always returns null so doesn't poulate $portsec_snmp
                if (! array_key_exists('cpsIfSecureLastMacAddrVlanId', $record)) {
                    unset($entry['cpsIfSecureLastMacAddrVlanId']);
                }
                // Checking that polled data exists and doesn't match. Else if polled data exists, insert a new record.
                if (array_key_exists('cpsIfPortSecurityEnable', $record) and $record != $entry) {
                    unset($record['port_id']);
                    //echo "Updating\n";
                    $update->where('port_id', $port_id)->update($record);
                }
            } elseif (array_key_exists('cpsIfPortSecurityEnable', $record)) {
                //$update->where('port_id', $port_id)->update($record);
                //echo "Creating\n";
                $update->create($record);
            }
        }

        return $portsec;
=======
    private function mapToEntPhysical(array &$data): array
    {
        // try DB first
        $dbMap = $this->getDevice()->entityPhysical;
        if ($dbMap->isNotEmpty()) {
            foreach ($data as $ifIndex => $_unused) {
                foreach ($dbMap as $entPhys) {
                    if ($entPhys->ifIndex === $ifIndex) {
                        $data[$ifIndex]['entPhysicalIndex'] = $entPhys->entPhysicalIndex;
                        $data[$ifIndex]['entPhysicalDescr'] = $entPhys->entPhysicalDescr;
                        break;
                    }
                }
            }

            return $data;
        }

        $entPhys = \SnmpQuery::walk('ENTITY-MIB::entPhysicalDescr')->table(1);

        // fixup incorrect/missing entPhysicalIndex mapping (doesn't use entAliasMappingIdentifier for some reason)
        foreach ($data as $ifIndex => $_unused) {
            foreach ($entPhys as $entIndex => $ent) {
                $descr = $ent['ENTITY-MIB::entPhysicalDescr'];
                unset($entPhys[$entIndex]); // only use each one once

                if (str_ends_with($descr, 'Radio')) {
                    d_echo("Mapping entPhysicalIndex $entIndex to ifIndex $ifIndex\n");
                    $data[$ifIndex]['entPhysicalIndex'] = $entIndex;
                    $data[$ifIndex]['entPhysicalDescr'] = $descr;
                    break;
                }
            }
        }

        return $data;
>>>>>>> 4cbc88a538 (Cisco os discovery cleanups (#17868))
    }
}
=======
    public function pollPortSecurity(Collection $os): Collection
    {
        $portsec = $os;

        return $portsec;
    }
}
>>>>>>> abab5288e0 (Adding inheritance and interface, and optimizing polling.)
