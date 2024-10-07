<?php
/*
 * PortSecurity.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2023 Michael Adams
 * @author     Michael Adams <mradams@ilstu.edu>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Facades\DB;
use LibreNMS\Config;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use App\Observers\ModuleModelObserver;
use SnmpQuery;
use LibreNMS\Interfaces\Polling\PortSecurityPolling;

class PortSecurity implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof PortSecurityDiscovery;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $this->poll($os, app('Datastore'));
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof PortSecurityPolling;
    }

    /**
     * Poll data for this module and update the DB
     *
     * @param  \LibreNMS\OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if ($os->getDevice()->portSecurity->isEmpty()) {
            return;
        }
        if ($os instanceof PortSecurityPolling) {
            // Polling for current data
            $device = $os->getDevice()->toArray();
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

                if (array_key_exists($if_index,  $portsec_snmp)) {
                    $portsec_snmp[$if_index]['port_id'] = $port_id;
                    $portsec_snmp[$if_index]['device_id'] = $device_id;
                }
            }

            // Assigning port_id and device_id to SNMP array for comparison
            $portsec = $os->pollPortSecurity($device->portSecurity);
            $portsec_db = $portsec->makeHidden('laravel_through_key');
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

                if ($entry) {
                    $entry = $entry->toArray();
                    unset($entry['id']);
                    // This OID currently always returns null so doesn't poulate $portsec_snmp
                    if (!array_key_exists('cpsIfSecureLastMacAddrVlanId', $record)) {
                        unset($entry['cpsIfSecureLastMacAddrVlanId']);
                    }
                    // Checking that polled data exists and doesn't
                    if (array_key_exists('cpsIfPortSecurityEnable', $record) and $record != $entry) {
                        unset($record['port_id']);
                        $update->where('port_id', $port_id)->update($record);
                    }
                }
                elseif (array_key_exists('cpsIfPortSecurityEnable', $record)) {
                    $update->where('port_id', $port_id)->update($record);
                }
            }
            ModuleModelObserver::observe(\App\Models\PortSecurity::class);
            $this->syncModels($device, 'portSecurity', $os->pollPortSecurity($portsec));
        }
        return;
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): void
    {
        $device->portSecurity()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'PortSecurity' => $device->portSecurity()->orderBy('port_id')
                ->get()->map->makeHidden(['id', 'device_id']),
        ];
    }
}