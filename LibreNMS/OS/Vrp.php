<?php
/**
 * Vrp.php
 *
 * Huawei VRP
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\AccessPoint;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Mempool;
use App\Models\PortsNac;
use App\Models\Sla;
use App\Models\Transceiver;
use App\Observers\ModuleModelObserver;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\OS;
use LibreNMS\OS\Traits\EntityMib;
use LibreNMS\RRD\RrdDefinition;

class Vrp extends OS implements
    MempoolsDiscovery,
    OSPolling,
    ProcessorDiscovery,
    NacPolling,
    WirelessApCountDiscovery,
    WirelessClientsDiscovery,
    SlaDiscovery,
    SlaPolling,
    TransceiverDiscovery,
    OSDiscovery
{
    use SyncsModels;
    use EntityMib {EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical; }

    public function discoverEntityPhysical(): Collection
    {
        // normal ENTITY-MIB collection
        $inventory = $this->discoverBaseEntityPhysical();

        // add additional data from Huawei MIBs
        $extra = \SnmpQuery::walk([
            'HUAWEI-ENTITY-EXTENT-MIB::hwEntityBoardType',
            'HUAWEI-ENTITY-EXTENT-MIB::hwEntityBomEnDesc',
        ])->table(1);

        $inventory->each(function (EntPhysical $entry) use ($extra) {
            if (isset($entry->entPhysicalIndex)) {
                if (! empty($extra[$entry->entPhysicalIndex]['HUAWEI-ENTITY-EXTENT-MIB::hwEntityBomEnDesc'])) {
                    $entry->entPhysicalDescr = $extra[$entry->entPhysicalIndex]['HUAWEI-ENTITY-EXTENT-MIB::hwEntityBomEnDesc'];
                }

                if (! empty($extra[$entry->entPhysicalIndex]['HUAWEI-ENTITY-EXTENT-MIB::hwEntityBoardType'])) {
                    $entry->entPhysicalModelName = $extra[$entry->entPhysicalIndex]['HUAWEI-ENTITY-EXTENT-MIB::hwEntityBoardType'];
                }
            }
        });

        return $inventory;
    }

    public function discoverTransceivers(): Collection
    {
        // Get a map of ifIndex to port_id for proper association with ports
        $ifIndexToPortId = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

        // EntityPhysicalIndex to ifIndex
        $entityToIfIndex = $this->getIfIndexEntPhysicalMap();

        // Walk through the MIB table for transceiver information
        return \SnmpQuery::walk('HUAWEI-ENTITY-EXTENT-MIB::hwOpticalModuleInfoTable')->mapTable(function ($data, $entIndex) use ($entityToIfIndex, $ifIndexToPortId) {
            // Skip inactive transceivers
            if ($data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalType'] === 'inactive') {
                return null;
            }
            if ($data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalMode'] == 1) {
                return null;
            }

            // Skip when it is not a plugable optic
            if ($data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalType'] === '0') {
                return null;
            }

            // Handle cases where required data might not be available (fallback to null)
            $connector = $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalConnectType'] ?? null;
            if ($connector == '-') {
                $connector = null;
            }

            $distance = $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalTransferDistance'] ?? '';
            if (preg_match_all("/(([0-9]+)\([^\)]+\))+/i", $distance, $matches)) {
                $distance = intval(max($matches[2]));
            } else {
                $distance = intval($distance);
            }
            if ($distance <= 0) {
                $distance = $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalTransDistance'] ?? 0;
            }
            if ($distance <= 0) {
                $distance = null;
            }
            $wavelength = $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalWaveLengthExact'] ?? 0;
            if ($wavelength <= 0) {
                $wavelength = $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalWaveLength'] ?? 0;
            }
            if ($wavelength <= 0) {
                $wavelength = null;
            }
            $ifIndex = $entityToIfIndex[$entIndex];
            $port_id = $ifIndexToPortId->get($ifIndex) ?? null;
            if (is_null($port_id)) {
                // Invalid
                return null;
            }

            $type = $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalType'] ?? null;
            $typeToDesc = ['unknown', 'sc', 'gbic', 'sfp', 'esfp', 'rj45', 'xfp', 'xenpak', 'transponder', 'cfp', 'smb', 'sfpplus', 'cxp', 'qsfp', 'qsfpplus', 'cfp2', 'dwdmsfp', 'msa100glh', 'gps', 'csfp', 'cfp4', 'qsfp28', 'sfpsfpplus', 'gponsfp', 'cfp8', 'sfp28', 'qsfpdd', 'cfp2dco', 'sfp56', 'qsfp56', 'oa'];

            $mode = $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalMode'] ?? '';
            $modeToText = [null, 'notSupported', 'singleMode', 'multiMode5', 'multiMode6', 'noValue', 'gpsMode'];
            if (isset($modeToText[$mode])) {
                $mode = ' ' . $modeToText[$mode];
            }
            if (! is_null($type) && isset($typeToDesc[$type])) {
                $type = $typeToDesc[$type];
            }
            $entityType = $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalTransType'] ?? null;
            if (! is_null($type) && ! is_null($entityType)) {
                $type .= " ($entityType)";
            } else {
                $type = $type ?? $entityType;
            }
            if (! is_null($type)) {
                $type .= $mode;
            }

            if (empty($data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalVenderName']) && empty($data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalVenderPn']) && empty($data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalVendorSn'])) {
                return null; //Probably no transceiver around here
            }

            // Create a new Transceiver object with the retrieved data
            return new Transceiver([
                'port_id' => $port_id,
                'index' => $entIndex,
                'vendor' => $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalVenderName'] ?? null,
                'type' => $type,
                'model' => $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalVenderPn'] ?? null,
                'serial' => $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalVendorSn'] ?? null,
                'connector' => $connector,
                'revision' => null,
                'cable' => null,
                'distance' => $distance,
                'date' => $data['HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalManufacturedDate'] ?? null,
                'wavelength' => $wavelength,
                'entity_physical_index' => $entIndex,
            ]);
        })->filter();  // Filter out null values
    }

    public function discoverMempools()
    {
        $mempools = new Collection();
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'hwEntityMemUsage', [], 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'hwEntityMemSize', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'hwEntityBomEnDesc', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'hwEntityMemSizeMega', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'entPhysicalName', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');

        foreach (Arr::wrap($mempools_array) as $index => $entry) {
            $size = empty($entry['hwEntityMemSizeMega']) ? ($entry['hwEntityMemSize'] ?? null) : $entry['hwEntityMemSizeMega'];
            $descr = empty($entry['entPhysicalName']) ? ($entry['hwEntityBomEnDesc'] ?? null) : $entry['entPhysicalName'];

            if ($size != 0 && $descr && ! Str::contains($descr, 'No') && ! Str::contains($entry['hwEntityMemUsage'], 'No')) {
                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'vrp',
                    'mempool_class' => 'system',
                    'mempool_precision' => empty($entry['hwEntityMemSizeMega']) ? 1 : 1048576,
                    'mempool_descr' => substr("$descr Memory", 0, 64),
                    'mempool_perc_oid' => ".1.3.6.1.4.1.2011.5.25.31.1.1.1.1.7.$index",
                    'mempool_perc_warn' => 90,
                ]))->fillUsage(null, $size, null, $entry['hwEntityMemUsage']));
            }
        }

        return $mempools;
    }

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        //Huawei VRP devices are not providing the HW description in a unified way
        preg_match('/Version (\S+)/', $device->sysDescr, $matches);
        $device->version = isset($matches[1]) ? ($matches[1] . ($device->version ? " ($device->version)" : '')) : null; // version from yaml sysDescr

        if ($device->version) {
            $patch = snmp_getnext($this->getDeviceArray(), 'HUAWEI-SYS-MAN-MIB::hwPatchVersion', '-OQv');
            if ($patch) {
                $device->version .= " [$patch]";
            }
        }

        if ($device->hardware && preg_match("/$device->hardware\S+/", $device->sysDescr, $matches)) {
            $device->hardware = $matches[0];
        }
    }

    public function pollOS(DataStorageInterface $datastore): void
    {
        // Polling the Wireless data TODO port to module
        $apTable = snmpwalk_group($this->getDeviceArray(), 'hwWlanApName', 'HUAWEI-WLAN-AP-MIB', 2);

        //Check for existence of at least 1 AP to continue the polling)
        if (! empty($apTable)) {
            $apTableOids = [
                'hwWlanApSn',
                'hwWlanApTypeInfo',
            ];
            foreach ($apTableOids as $apTableOid) {
                $apTable = snmpwalk_group($this->getDeviceArray(), $apTableOid, 'HUAWEI-WLAN-AP-MIB', 2, $apTable);
            }

            $apRadioTableOids = [ // hwWlanRadioInfoTable
                'hwWlanRadioMac',
                'hwWlanRadioChUtilizationRate',
                'hwWlanRadioChInterferenceRate',
                'hwWlanRadioActualEIRP',
                'hwWlanRadioType',
                'hwWlanRadioWorkingChannel',
            ];

            $clientPerRadio = [];
            $radioTable = [];
            foreach ($apRadioTableOids as $apRadioTableOid) {
                $radioTable = snmpwalk_group($this->getDeviceArray(), $apRadioTableOid, 'HUAWEI-WLAN-AP-RADIO-MIB', 2, $radioTable);
            }

            $numClients = 0;
            $vapInfoTable = snmpwalk_group($this->getDeviceArray(), 'hwWlanVapStaOnlineCnt', 'HUAWEI-WLAN-VAP-MIB', 3);
            foreach ($vapInfoTable as $ap_id => $ap) {
                //Convert mac address (hh:hh:hh:hh:hh:hh) to dec OID (ddd.ddd.ddd.ddd.ddd.ddd)
                //$a_index_oid = implode(".", array_map("hexdec", explode(":", $ap_id)));
                foreach ($ap as $r_id => $radio) {
                    foreach ($radio as $s_index => $ssid) {
                        $clientPerRadio[$ap_id][$r_id] = ($clientPerRadio[$ap_id][$r_id] ?? 0) + ($ssid['hwWlanVapStaOnlineCnt'] ?? 0);
                        $numClients += ($ssid['hwWlanVapStaOnlineCnt'] ?? 0);
                    }
                }
            }

            $numRadios = count($radioTable);

            $rrd_def = RrdDefinition::make()
                ->addDataset('NUMAPS', 'GAUGE', 0, 12500000000)
                ->addDataset('NUMCLIENTS', 'GAUGE', 0, 12500000000);

            $fields = [
                'NUMAPS' => $numRadios,
                'NUMCLIENTS' => $numClients,
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'vrp', $tags, $fields);

            $aps = new Collection;

            foreach ($radioTable as $ap_id => $ap) {
                foreach ($ap as $r_id => $radio) {
                    $channel = $radio['hwWlanRadioWorkingChannel'] ?? 0;
                    $mac = $radio['hwWlanRadioMac'] ?? '';
                    $name = ($apTable[$ap_id]['hwWlanApName'] ?? '') . ' Radio ' . $r_id;
                    $radionum = $r_id;
                    $txpow = $radio['hwWlanRadioActualEIRP'] ?? 0;
                    $interference = $radio['hwWlanRadioChInterferenceRate'] ?? 0;
                    $radioutil = $radio['hwWlanRadioChUtilizationRate'] ?? 0;
                    $radioutil = ($radioutil > 100 || $radioutil < 0) ? -1 : $radioutil;
                    $numasoclients = $clientPerRadio[$ap_id][$r_id] ?? 0;
                    $radio['hwWlanRadioType'] = $radio['hwWlanRadioType'] ?? 0;

                    if ($txpow > 127) {
                        // means the radio is disabled for some reason.
                        $txpow = 0;
                    }

                    $type = 'dot11';

                    if ($radio['hwWlanRadioType'] & 2) {
                        $type .= 'a';
                    }

                    if ($radio['hwWlanRadioType'] & 1) {
                        $type .= 'b';
                    }

                    if ($radio['hwWlanRadioType'] & 4) {
                        $type .= 'g';
                    }

                    if ($radio['hwWlanRadioType'] & 8) {
                        $type .= 'n';
                    }

                    if ($radio['hwWlanRadioType'] & 16) {
                        $type .= '_ac';
                    }

                    if ($radio['hwWlanRadioType'] & 32) {
                        $type .= '_ax';
                    }

                    // TODO
                    $numactbssid = 0;
                    $nummonbssid = 0;
                    $nummonclients = 0;

                    d_echo("  name: $name\n");
                    d_echo("  radionum: $radionum\n");
                    d_echo("  type: $type\n");
                    d_echo("  channel: $channel\n");
                    d_echo("  txpow: $txpow\n");
                    d_echo("  radioutil: $radioutil\n");
                    d_echo("  numasoclients: $numasoclients\n");
                    d_echo("  interference: $interference\n");

                    $rrd_name = ['arubaap', $name . $radionum];
                    $rrd_def = RrdDefinition::make()
                        ->addDataset('channel', 'GAUGE', 0, 200)
                        ->addDataset('txpow', 'GAUGE', 0, 200)
                        ->addDataset('radioutil', 'GAUGE', 0, 100)
                        ->addDataset('nummonclients', 'GAUGE', 0, 500)
                        ->addDataset('nummonbssid', 'GAUGE', 0, 200)
                        ->addDataset('numasoclients', 'GAUGE', 0, 500)
                        ->addDataset('interference', 'GAUGE', 0, 2000);

                    $fields = [
                        'channel' => $channel,
                        'txpow' => $txpow,
                        'radioutil' => $radioutil,
                        'nummonclients' => $nummonclients,
                        'nummonbssid' => $nummonbssid,
                        'numasoclients' => $numasoclients,
                        'interference' => $interference,
                    ];

                    $tags = compact('name', 'radionum', 'rrd_name', 'rrd_def');
                    $datastore->put($this->getDeviceArray(), 'arubaap', $tags, $fields);

                    $aps->push(new AccessPoint([
                        'device_id' => $this->getDeviceId(),
                        'name' => $name,
                        'radio_number' => $radionum,
                        'type' => $type,
                        'mac_addr' => $mac,
                        'channel' => $channel,
                        'txpow' => $txpow,
                        'radioutil' => $radioutil,
                        'numasoclients' => $numasoclients,
                        'nummonclients' => $nummonclients,
                        'numactbssid' => $numactbssid,
                        'nummonbssid' => $nummonbssid,
                        'interference' => $interference,
                    ]));
                }
            }

            ModuleModelObserver::observe(AccessPoint::class);
            $this->syncModels($this->getDevice(), 'accessPoints', $aps);
        }
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $device = $this->getDeviceArray();

        $processors_data = snmpwalk_cache_multi_oid($device, 'hwEntityCpuUsage', [], 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');

        if (! empty($processors_data)) {
            $processors_data = snmpwalk_cache_multi_oid($device, 'hwEntityMemSize', $processors_data, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
            $processors_data = snmpwalk_cache_multi_oid($device, 'hwEntityBomEnDesc', $processors_data, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        }

        d_echo($processors_data);

        $processors = [];
        foreach ($processors_data as $index => $entry) {
            if ($entry['hwEntityMemSize'] != 0) {
                d_echo($index . ' ' . $entry['hwEntityBomEnDesc'] . ' -> ' . $entry['hwEntityCpuUsage'] . ' -> ' . $entry['hwEntityMemSize'] . "\n");

                $usage_oid = '.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.5.' . $index;
                $descr = $entry['hwEntityBomEnDesc'];
                $usage = $entry['hwEntityCpuUsage'];

                if (empty($descr) || Str::contains($descr, 'No') || Str::contains($usage, 'No')) {
                    continue;
                }

                $processors[] = Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $usage_oid,
                    $index,
                    $descr,
                    1,
                    $usage
                );
            }
        }

        return $processors;
    }

    /**
     * Discover the Network Access Control informations (dot1X etc etc)
     */
    public function pollNac()
    {
        $nac = new Collection();
        // We collect the first table
        $portAuthSessionEntry = snmpwalk_cache_oid($this->getDeviceArray(), 'hwAccessInterface', [], 'HUAWEI-AAA-MIB');

        if (! empty($portAuthSessionEntry)) {
            // If it is not empty, lets add all the necessary OIDs
            $hwAccessOids = [
                'hwAccessMACAddress',
                'hwAccessDomain',
                'hwAccessUserName',
                'hwAccessIPAddress',
                'hwAccessType',
                'hwAccessAuthorizetype',
                'hwAccessSessionTimeout',
                'hwAccessOnlineTime',
                'hwAccessCurAuthenPlace',
                'hwAccessAuthtype',
                'hwAccessVLANID',
            ];
            foreach ($hwAccessOids as $hwAccessOid) {
                $portAuthSessionEntry = snmpwalk_cache_oid($this->getDeviceArray(), $hwAccessOid, $portAuthSessionEntry, 'HUAWEI-AAA-MIB');
            }
            // We cache a port_ifName -> port_id map
            $ifName_map = $this->getDevice()->ports()->pluck('port_id', 'ifName');

            // update the DB
            foreach ($portAuthSessionEntry as $authId => $portAuthSessionEntryParameters) {
                if (! array_key_exists('hwAccessInterface', $portAuthSessionEntryParameters) || ! array_key_exists('hwAccessMACAddress', $portAuthSessionEntryParameters)) {
                    continue;
                }
                $mac_address = strtolower(implode(array_map('zeropad', explode(':', $portAuthSessionEntryParameters['hwAccessMACAddress']))));
                $port_id = $ifName_map->get($portAuthSessionEntryParameters['hwAccessInterface'], 0);
                if ($port_id <= 0) {
                    continue; //this would happen for an SSH session for instance
                }
                $nac->put($mac_address, new PortsNac([
                    'port_id' => $ifName_map->get($portAuthSessionEntryParameters['hwAccessInterface'] ?? null, 0),
                    'mac_address' => $mac_address,
                    'auth_id' => $authId,
                    'domain' => $portAuthSessionEntryParameters['hwAccessDomain'] ?? '',
                    'username' => $portAuthSessionEntryParameters['hwAccessUserName'] ?? '',
                    'ip_address' => $portAuthSessionEntryParameters['hwAccessIPAddress'] ?? '',
                    'authz_by' => $portAuthSessionEntryParameters['hwAccessType'] ?? '',
                    'authz_status' => $portAuthSessionEntryParameters['hwAccessAuthorizetype'] ?? '',
                    'host_mode' => $portAuthSessionEntryParameters['hwAccessAuthType'] ?? 'default',
                    'timeout' => $portAuthSessionEntryParameters['hwAccessSessionTimeout'] ?? '',
                    'time_elapsed' => $portAuthSessionEntryParameters['hwAccessOnlineTime'] ?? null,
                    'authc_status' => $portAuthSessionEntryParameters['hwAccessCurAuthenPlace'] ?? '',
                    'method' => $portAuthSessionEntryParameters['hwAccessAuthtype'] ?? '',
                    'vlan' => $portAuthSessionEntryParameters['hwAccessVLANID'] ?? null,
                ]));
            }
        }

        return $nac;
    }

    public function discoverWirelessApCount()
    {
        $sensors = [];
        $ap_number = snmpwalk_cache_oid($this->getDeviceArray(), 'hwWlanCurJointApNum.0', [], 'HUAWEI-WLAN-GLOBAL-MIB');

        $sensors[] = new WirelessSensor(
            'ap-count',
            $this->getDeviceId(),
            '.1.3.6.1.4.1.2011.6.139.12.1.2.1.0',
            'vrp-ap-count',
            'ap-count',
            'AP Count',
            $ap_number[0]['hwWlanCurJointApNum']
        );

        return $sensors;
    }

    public function discoverWirelessClients()
    {
        $sensors = [];

        $staTable = snmpwalk_cache_oid($this->getDeviceArray(), 'hwWlanSsid2gStaCnt', [], 'HUAWEI-WLAN-VAP-MIB');
        $staTable = snmpwalk_cache_oid($this->getDeviceArray(), 'hwWlanSsid5gStaCnt', $staTable, 'HUAWEI-WLAN-VAP-MIB');

        //Map OIDs and description
        $oidMap = [
            'hwWlanSsid5gStaCnt' => '.1.3.6.1.4.1.2011.6.139.17.1.2.1.3.',
            'hwWlanSsid2gStaCnt' => '.1.3.6.1.4.1.2011.6.139.17.1.2.1.2.',
        ];
        $descrMap = [
            'hwWlanSsid5gStaCnt' => '5 GHz',
            'hwWlanSsid2gStaCnt' => '2.4 GHz',
        ];
        $ssid_total_oid_array = []; // keep all OIDs so we can compute the total of all STA

        foreach ($staTable as $ssid => $sta) {
            //Convert string to num_oid
            $numSsid = strlen($ssid) . '.' . implode('.', unpack('c*', $ssid));
            $ssid_oid_array = []; // keep all OIDs of different freqs for a single SSID, to compute each SSID sta count, all freqs included
            foreach ($sta as $staFreq => $count) {
                $oid = $oidMap[$staFreq] . $numSsid;
                $ssid_oid_array[] = $oid;
                $ssid_total_oid_array[] = $oid;
                $sensors[] = new WirelessSensor(
                    'clients',
                    $this->getDeviceId(),
                    $oid,
                    'vrpi-clients',
                    $staFreq . '-' . $ssid,
                    'SSID: ' . $ssid . ' (' . $descrMap[$staFreq] . ')',
                    $count,
                    1,
                    1,
                    'sum'
                );
            }

            // And we add a sensor with all frequencies for each SSID
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $ssid_oid_array,
                'vrp-clients',
                'total-' . $ssid,
                'SSID: ' . $ssid,
                0,
                1,
                1,
                'sum'
            );
        }
        if (count($ssid_total_oid_array) > 0) {
            // We have at least 1 SSID, so we can count the total of STA
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $ssid_total_oid_array,
                'vrp-clients',
                'total-all-ssids',
                'Total Clients',
                0,
                1,
                1,
                'sum'
            );
        }

        return $sensors;
    }

    public function discoverSlas(): Collection
    {
        $slas = new Collection();
        // Get the index of the last finished test
        // NQA-MIB::nqaScheduleLastFinishIndex

        $sla_table = snmpwalk_cache_oid($this->getDeviceArray(), 'pingCtlTable', [], 'DISMAN-PING-MIB');

        if (! empty($sla_table)) {
            $sla_table = snmpwalk_cache_oid($this->getDeviceArray(), 'nqaAdminCtrlType', $sla_table, 'NQA-MIB');
            $sla_table = snmpwalk_cache_oid($this->getDeviceArray(), 'nqaAdminParaTimeUnit', $sla_table, 'NQA-MIB');
            $sla_table = snmpwalk_cache_oid($this->getDeviceArray(), 'nqaScheduleLastFinishIndex', $sla_table, 'NQA-MIB');
        }

        foreach ($sla_table as $sla_key => $sla_config) {
            [$owner, $test] = explode('.', $sla_key, 2);

            $slas->push(new Sla([
                'sla_nr' => hexdec(hash('crc32', $owner . $test)), // indexed by owner+test, convert to int
                'owner' => $owner,
                'tag' => $test,
                'rtt_type' => $sla_config['nqaAdminCtrlType'] ?? '',
                'rtt' => isset($sla_config['pingResultsAverageRtt']) ? $sla_config['pingResultsAverageRtt'] / 1000 : null,
                'status' => ($sla_config['pingCtlAdminStatus'] == 'enabled') ? 1 : 0,
                'opstatus' => ($sla_config['pingCtlRowStatus'] == 'active') ? 0 : 2,
            ]));
        }

        return $slas;
    }

    public function pollSlas($slas): void
    {
        $device = $this->getDeviceArray();

        // Go get some data from the device.
        $data = snmpwalk_group($device, 'pingCtlRowStatus', 'DISMAN-PING-MIB', 2);
        $data = snmpwalk_group($device, 'pingResultsProbeResponses', 'DISMAN-PING-MIB', 2, $data);
        $data = snmpwalk_group($device, 'pingResultsSentProbes', 'DISMAN-PING-MIB', 2, $data);
        //$data = snmpwalk_group($device, 'nqaScheduleLastFinishIndex', 'NQA-MIB', 2, $data);
        //$data = snmpwalk_group($device, 'pingResultsMinRtt', 'DISMAN-PING-MIB', 2, $data);
        //$data = snmpwalk_group($device, 'pingResultsMaxRtt', 'DISMAN-PING-MIB', 2, $data);
        $data = snmpwalk_group($device, 'pingResultsAverageRtt', 'DISMAN-PING-MIB', 2, $data);

        // Get the needed information
        foreach ($slas as $sla) {
            $sla_nr = $sla->sla_nr;
            $rtt_type = $sla->rtt_type;
            $owner = $sla->owner;
            $test = $sla->tag;
            $divisor = 1; //values are already returned in ms, and RRD expects them in ms

            // Use DISMAN-PING Status codes. 0=Good 2=Critical
            $sla->opstatus = ($data[$owner][$test]['pingCtlRowStatus'] ?? null) == '1' ? 0 : 2;

            $sla->rtt = ($data[$owner][$test]['pingResultsAverageRtt'] ?? 0) / $divisor;
            $time = Carbon::parse($data[$owner][$test]['pingResultsLastGoodProbe'] ?? null)->toDateTimeString();
            Log::info('SLA : ' . $rtt_type . ' ' . $owner . ' ' . $test . '... ' . $sla->rtt . 'ms at ' . $time);

            $collected = ['rtt' => $sla->rtt];

            // Let's gather some per-type fields.
            switch ($rtt_type) {
                case 'icmpAppl':
                    $icmp = [
                        //'MinRtt' => $data[$owner][$test]['pingResultsMinRtt'] / $divisor,
                        //'MaxRtt' => $data[$owner][$test]['pingResultsMaxRtt'] / $divisor,
                        'ProbeResponses' => $data[$owner][$test]['pingResultsProbeResponses'] ?? null,
                        'ProbeLoss' => (int) ($data[$owner][$test]['pingResultsSentProbes'] ?? 0) - (int) ($data[$owner][$test]['pingResultsProbeResponses'] ?? 0),
                    ];
                    $rrd_name = ['sla', $sla_nr, $rtt_type];
                    $rrd_def = RrdDefinition::make()
                        //->addDataset('MinRtt', 'GAUGE', 0, 300000)
                        //->addDataset('MaxRtt', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeResponses', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeLoss', 'GAUGE', 0, 300000);
                    $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                    app('Datastore')->put($device, 'sla', $tags, $icmp);
                    $collected = array_merge($collected, $icmp);
                    break;
            }

            d_echo('The following datasources were collected for #' . $sla->sla_nr . ":\n");
            d_echo($collected);
        }
    }
}
