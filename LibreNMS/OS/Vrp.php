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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\Mempool;
use App\Models\PortsNac;
use App\Models\Sla;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\OS;
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
    OSDiscovery
{
    public function discoverMempools()
    {
        $mempools = collect();
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'hwEntityMemUsage', [], 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'hwEntityMemSize', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'hwEntityBomEnDesc', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'hwEntityMemSizeMega', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        $mempools_array = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'entPhysicalName', $mempools_array, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');

        foreach (Arr::wrap($mempools_array) as $index => $entry) {
            $size = empty($entry['hwEntityMemSizeMega']) ? $entry['hwEntityMemSize'] : $entry['hwEntityMemSizeMega'];
            $descr = empty($entry['entPhysicalName']) ? $entry['hwEntityBomEnDesc'] : $entry['entPhysicalName'];

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

    public function pollOS()
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
                'hwWlanRadioFreqType',
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
                        $clientPerRadio[$ap_id][$r_id] += $ssid['hwWlanVapStaOnlineCnt'];
                        $numClients += $ssid['hwWlanVapStaOnlineCnt'];
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
            data_update($this->getDeviceArray(), 'vrp', $tags, $fields);

            $ap_db = dbFetchRows('SELECT * FROM `access_points` WHERE `device_id` = ?', [$this->getDeviceArray()['device_id']]);

            foreach ($radioTable as $ap_id => $ap) {
                foreach ($ap as $r_id => $radio) {
                    $channel = $radio['hwWlanRadioWorkingChannel'];
                    $mac = $radio['hwWlanRadioMac'];
                    $name = $apTable[$ap_id]['hwWlanApName'] . ' Radio ' . $r_id;
                    $radionum = $r_id;
                    $txpow = $radio['hwWlanRadioActualEIRP'];
                    $interference = $radio['hwWlanRadioChInterferenceRate'];
                    $radioutil = $radio['hwWlanRadioChUtilizationRate'];
                    $numasoclients = $clientPerRadio[$ap_id][$r_id];

                    switch ($radio['hwWlanRadioFreqType']) {
                        case 1:
                            $type = '2.4Ghz';
                            break;
                        case 2:
                            $type = '5Ghz';
                            break;
                        default:
                            $type = 'unknown (huawei ' . $radio['hwWlanRadioFreqType'] . ')';
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
                    data_update($this->getDeviceArray(), 'arubaap', $tags, $fields);

                    $foundid = 0;

                    for ($z = 0; $z < sizeof($ap_db); $z++) {
                        if ($ap_db[$z]['name'] == $name && $ap_db[$z]['radio_number'] == $radionum) {
                            $foundid = $ap_db[$z]['accesspoint_id'];
                            $ap_db[$z]['seen'] = 1;
                            continue;
                        }
                    }

                    if ($foundid == 0) {
                        $ap_id = dbInsert(
                            [
                                'device_id' => $this->getDeviceArray()['device_id'],
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
                            ],
                            'access_points'
                        );
                    } else {
                        dbUpdate(
                            [
                                'mac_addr' => $mac,
                                'type' => $type,
                                'deleted' => 0,
                                'channel' => $channel,
                                'txpow' => $txpow,
                                'radioutil' => $radioutil,
                                'numasoclients' => $numasoclients,
                                'nummonclients' => $nummonclients,
                                'numactbssid' => $numactbssid,
                                'nummonbssid' => $nummonbssid,
                                'interference' => $interference,
                            ],
                            'access_points',
                            '`accesspoint_id` = ?',
                            [$foundid]
                        );
                    }
                }//end foreach 1
            }//end foreach 2

            for ($z = 0; $z < sizeof($ap_db); $z++) {
                if (! isset($ap_db[$z]['seen']) && $ap_db[$z]['deleted'] == 0) {
                    dbUpdate(['deleted' => 1], 'access_points', '`accesspoint_id` = ?', [$ap_db[$z]['accesspoint_id']]);
                }
            }
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
        $nac = collect();
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
                $mac_address = strtolower(implode(array_map('zeropad', explode(':', $portAuthSessionEntryParameters['hwAccessMACAddress']))));
                $port_id = $ifName_map->get($portAuthSessionEntryParameters['hwAccessInterface'], 0);
                if ($port_id <= 0) {
                    continue; //this would happen for an SSH session for instance
                }
                $nac->put($mac_address, new PortsNac([
                    'port_id' => $ifName_map->get($portAuthSessionEntryParameters['hwAccessInterface'], 0),
                    'mac_address' => $mac_address,
                    'auth_id' => $authId,
                    'domain' => $portAuthSessionEntryParameters['hwAccessDomain'],
                    'username' => '' . $portAuthSessionEntryParameters['hwAccessUserName'],
                    'ip_address' => $portAuthSessionEntryParameters['hwAccessIPAddress'],
                    'authz_by' => '' . $portAuthSessionEntryParameters['hwAccessType'],
                    'authz_status' => '' . $portAuthSessionEntryParameters['hwAccessAuthorizetype'],
                    'host_mode' => is_null($portAuthSessionEntryParameters['hwAccessAuthType']) ? 'default' : $portAuthSessionEntryParameters['hwAccessAuthType'],
                    'timeout' => $portAuthSessionEntryParameters['hwAccessSessionTimeout'],
                    'time_elapsed' => $portAuthSessionEntryParameters['hwAccessOnlineTime'],
                    'authc_status' => $portAuthSessionEntryParameters['hwAccessCurAuthenPlace'],
                    'method' => '' . $portAuthSessionEntryParameters['hwAccessAuthtype'],
                    'vlan' => $portAuthSessionEntryParameters['hwAccessVLANID'],
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

    public function discoverSlas()
    {
        $slas = collect();
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
                'rtt_type' => $sla_config['nqaAdminCtrlType'],
                'rtt' => isset($sla_config['pingResultsAverageRtt']) ? $sla_config['pingResultsAverageRtt'] / 1000 : null,
                'status' => ($sla_config['pingCtlAdminStatus'] == 'enabled') ? 1 : 0,
                'opstatus' => ($sla_config['pingCtlRowStatus'] == 'active') ? 0 : 2,
            ]));
        }

        return $slas;
    }

    public function pollSlas($slas)
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
            $sla->opstatus = $data[$owner][$test]['pingCtlRowStatus'] == '1' ? 0 : 2;

            $sla->rtt = $data[$owner][$test]['pingResultsAverageRtt'] / $divisor;
            $time = Carbon::parse($data[$owner][$test]['pingResultsLastGoodProbe'])->toDateTimeString();
            echo 'SLA : ' . $rtt_type . ' ' . $owner . ' ' . $test . '... ' . $sla->rtt . 'ms at ' . $time . "\n";

            $fields = [
                'rtt' => $sla->rtt,
            ];

            // The base RRD
            $rrd_name = ['sla', $sla['sla_nr']];
            $rrd_def = RrdDefinition::make()->addDataset('rtt', 'GAUGE', 0, 300000);
            $tags = compact('sla_nr', 'rrd_name', 'rrd_def');
            data_update($device, 'sla', $tags, $fields);

            // Let's gather some per-type fields.
            switch ($rtt_type) {
                case 'icmpAppl':
                    $icmp = [
                        //'MinRtt' => $data[$owner][$test]['pingResultsMinRtt'] / $divisor,
                        //'MaxRtt' => $data[$owner][$test]['pingResultsMaxRtt'] / $divisor,
                        'ProbeResponses' => $data[$owner][$test]['pingResultsProbeResponses'],
                        'ProbeLoss' => (int) $data[$owner][$test]['pingResultsSentProbes'] - (int) $data[$owner][$test]['pingResultsProbeResponses'],
                    ];
                    $rrd_name = ['sla', $sla_nr, $rtt_type];
                    $rrd_def = RrdDefinition::make()
                        //->addDataset('MinRtt', 'GAUGE', 0, 300000)
                        //->addDataset('MaxRtt', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeResponses', 'GAUGE', 0, 300000)
                        ->addDataset('ProbeLoss', 'GAUGE', 0, 300000);
                    $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                    data_update($device, 'sla', $tags, $icmp);
                    $fields = array_merge($fields, $icmp);
                    break;
            }

            d_echo('The following datasources were collected for #' . $sla['sla_nr'] . ":\n");
            d_echo($fields);
        }
    }
}
