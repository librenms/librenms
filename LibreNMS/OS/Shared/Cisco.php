<?php
/**
 * Cisco.php
 *
 * Base Cisco OS for Cisco based devices
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
 * @copyright  2018 Jose Augusto Cardoso
 */

namespace LibreNMS\OS\Shared;

use App\Models\Device;
use App\Models\Mempool;
use App\Models\PortsNac;
use App\Models\Sla;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\OS;
use LibreNMS\OS\Traits\YamlOSDiscovery;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IP;

class Cisco extends OS implements OSDiscovery, SlaDiscovery, ProcessorDiscovery, MempoolsDiscovery, NacPolling, SlaPolling
{
    use YamlOSDiscovery {
        YamlOSDiscovery::discoverOS as discoverYamlOS;
    }

    public function discoverOS(Device $device): void
    {
        // yaml discovery overrides this
        if ($this->hasYamlDiscovery('os')) {
            $this->discoverYamlOS($device);

            return;
        }

        $device->serial = $this->getMainSerial();
        $hardware = null;

        if (preg_match('/^Cisco IOS Software, .+? Software \([^\-]+-([^\-]+)-\w\),.+?Version ([^, ]+)/', $device->sysDescr, $regexp_result)) {
            $device->features = $regexp_result[1];
            $device->version = $regexp_result[2];
        } elseif (preg_match('/Cisco Internetwork Operating System Software\s+IOS \(tm\) [^ ]+ Software \([^\-]+-([^\-]+)-\w\),.+?Version ([^, ]+)/', $device->sysDescr, $regexp_result)) {
            $device->features = $regexp_result[1];
            $device->version = $regexp_result[2];
        } elseif (preg_match('/^Cisco IOS Software \[([^\]]+)\],.+Software \(([^\)]+)\), Version ([^, ]+)/', $device->sysDescr, $regexp_result)) {
            $device->features = $regexp_result[1];
            $device->version = $regexp_result[2] . ' ' . $regexp_result[3];
        } elseif (preg_match('/^Cisco IOS Software.*?, .+? Software(\, )?([\s\w\d]+)? \([^\-]+-([\w\d]+)-\w\), Version ([^,]+)/', $device->sysDescr, $regexp_result)) {
            $device->features = $regexp_result[3];
            $device->version = $regexp_result[4];
            $hardware = $regexp_result[2];
            $tmp = preg_split('/\\r\\n|\\r|\\n/', $device->version);
            if (! empty($tmp[0])) {
                $device->version = $tmp[0];
            }
        }

        $oids = [
            'entPhysicalModelName.1',
            'entPhysicalContainedIn.1',
            'entPhysicalName.1',
            'entPhysicalSoftwareRev.1',
            'entPhysicalModelName.1000',
            'entPhysicalModelName.1001',
            'entPhysicalContainedIn.1000',
            'entPhysicalContainedIn.1001',
        ];

        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');

        if (isset($data[1]['entPhysicalContainedIn']) && $data[1]['entPhysicalContainedIn'] == '0') {
            if (! empty($data[1]['entPhysicalSoftwareRev'])) {
                $device->version = $data[1]['entPhysicalSoftwareRev'];
            }
            if (! empty($data[1]['entPhysicalName'])) {
                $hardware = $data[1]['entPhysicalName'];
            }
            if (! empty($data[1]['entPhysicalModelName'])) {
                $hardware = $data[1]['entPhysicalModelName'];
            }
        }

        if ((empty($hardware) || preg_match('/Switch System/', $hardware)) && ! empty($data[1000]['entPhysicalModelName'])) {
            $hardware = $data[1000]['entPhysicalModelName'];
        } elseif (empty($hardware) && ! empty($data[1000]['entPhysicalContainedIn'])) {
            $hardware = $data[$data[1000]['entPhysicalContainedIn']]['entPhysicalName'];
        } elseif ((preg_match('/stack/i', $hardware) || empty($hardware)) && ! empty($data[1001]['entPhysicalModelName'])) {
            $hardware = $data[1001]['entPhysicalModelName'];
        } elseif (empty($hardware) && ! empty($data[1001]['entPhysicalContainedIn'])) {
            $hardware = $data[$data[1001]['entPhysicalContainedIn']]['entPhysicalName'];
        }

        $device->hardware = $hardware ?: snmp_translate($device->sysObjectID, 'SNMPv2-MIB:CISCO-PRODUCTS-MIB', 'cisco');
    }

    public function discoverMempools()
    {
        if ($this->hasYamlDiscovery('mempools')) {
            return parent::discoverMempools(); // yaml
        }

        $mempools = collect();
        $cemp = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'cempMemPoolTable', [], 'CISCO-ENHANCED-MEMPOOL-MIB');

        foreach (Arr::wrap($cemp) as $index => $entry) {
            if (is_numeric($entry['cempMemPoolUsed']) && $entry['cempMemPoolValid'] == 'true') {
                [$entPhysicalIndex] = explode('.', $index);
                $entPhysicalName = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB')[$entPhysicalIndex];
                $descr = ucwords($entPhysicalName . ' - ' . $entry['cempMemPoolName']);
                $descr = trim(str_replace(['Cisco ', 'Network Processing Engine'], '', $descr), ' -');

                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'entPhysicalIndex' => $entPhysicalIndex,
                    'mempool_type' => 'cemp',
                    'mempool_class' => 'system',
                    'mempool_precision' => 1,
                    'mempool_descr' => $descr,
                    'mempool_used_oid' => isset($entry['cempMemPoolHCUsed']) ? ".1.3.6.1.4.1.9.9.221.1.1.1.1.18.$index" : ".1.3.6.1.4.1.9.9.221.1.1.1.1.7.$index",
                    'mempool_free_oid' => isset($entry['cempMemPoolHCFree']) ? ".1.3.6.1.4.1.9.9.221.1.1.1.1.20.$index" : ".1.3.6.1.4.1.9.9.221.1.1.1.1.8.$index",
                    'mempool_perc_warn' => 90,
                    'mempool_largestfree' => $entry['cempMemPoolHCLargestFree'] ?? $entry['cempMemPoolLargestFree'] ?? null,
                    'mempool_lowestfree' => $entry['cempMemPoolHCLowestFree'] ?? $entry['cempMemPoolLowestFree'] ?? null,
                ]))->fillUsage($entry['cempMemPoolHCUsed'] ?? $entry['cempMemPoolUsed'], null, $entry['cempMemPoolHCFree'] ?? $entry['cempMemPoolFree']));
            }
        }

        if ($mempools->isNotEmpty()) {
            return $mempools;
        }

        $cmp = snmpwalk_cache_oid($this->getDeviceArray(), 'ciscoMemoryPool', [], 'CISCO-MEMORY-POOL-MIB');
        foreach (Arr::wrap($cmp) as $index => $entry) {
            if (is_numeric($entry['ciscoMemoryPoolUsed']) && is_numeric($index)) {
                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'cmp',
                    'mempool_class' => 'system',
                    'mempool_precision' => 1,
                    'mempool_descr' => $entry['ciscoMemoryPoolName'],
                    'mempool_used_oid' => ".1.3.6.1.4.1.9.9.48.1.1.1.5.$index",
                    'mempool_free_oid' => ".1.3.6.1.4.1.9.9.48.1.1.1.6.$index",
                    'mempool_perc_warn' => 90,
                    'mempool_largestfree' => $entry['ciscoMemoryPoolLargestFree'] ?? null,
                ]))->fillUsage($entry['ciscoMemoryPoolUsed'], null, $entry['ciscoMemoryPoolFree']));
            }
        }

        if ($mempools->isNotEmpty()) {
            return $mempools;
        }

        $cpm = $this->getCacheTable('cpmCPUTotalTable', 'CISCO-PROCESS-MIB');

        $count = 0;
        foreach (Arr::wrap($cpm) as $index => $entry) {
            $count++;
            if (is_numeric($entry['cpmCPUMemoryFree']) && is_numeric($entry['cpmCPUMemoryFree'])) {
                $cpu = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB')[$entry['cpmCPUTotalPhysicalIndex'] ?? 'none'] ?? "Processor $index";

                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'cpm',
                    'mempool_class' => 'system',
                    'mempool_precision' => 1024,
                    'mempool_descr' => "$cpu Memory",
                    'mempool_used_oid' => empty($entry['cpmCPUMemoryHCUsed']) ? ".1.3.6.1.4.1.9.9.109.1.1.1.1.12.$index" : ".1.3.6.1.4.1.9.9.109.1.1.1.1.17.$index",
                    'mempool_free_oid' => empty($entry['cpmCPUMemoryHCFree']) ? ".1.3.6.1.4.1.9.9.109.1.1.1.1.13.$index" : ".1.3.6.1.4.1.9.9.109.1.1.1.1.19.$index",
                    'mempool_perc_warn' => 90,
                    'mempool_lowestfree' => $entry['cpmCPUMemoryHCLowest'] ?? $entry['cpmCPUMemoryLowest'] ?? null,
                ]))->fillUsage(
                    empty($entry['cpmCPUMemoryHCUsed']) ? $entry['cpmCPUMemoryUsed'] : $entry['cpmCPUMemoryHCUsed'],
                    null,
                    empty($entry['cpmCPUMemoryHCFree']) ? $entry['cpmCPUMemoryFree'] : $entry['cpmCPUMemoryHCFree']
                ));
            }
        }

        return $mempools;
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors_data = $this->getCacheTable('cpmCPUTotalTable', 'CISCO-PROCESS-MIB');
        $processors_data = snmpwalk_group($this->getDeviceArray(), 'cpmCoreTable', 'CISCO-PROCESS-MIB', 1, $processors_data);
        $processors = [];

        foreach ($processors_data as $index => $entry) {
            if (is_numeric($entry['cpmCPUTotal5minRev'])) {
                $usage_oid = '.1.3.6.1.4.1.9.9.109.1.1.1.1.8.' . $index;
                $usage = $entry['cpmCPUTotal5minRev'];
            } elseif (is_numeric($entry['cpmCPUTotal5min'])) {
                $usage_oid = '.1.3.6.1.4.1.9.9.109.1.1.1.1.5.' . $index;
                $usage = $entry['cpmCPUTotal5min'];
            } else {
                continue; // skip bad data
            }

            if (isset($entry['cpmCPUTotalPhysicalIndex'])) {
                $descr = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB')[$entry['cpmCPUTotalPhysicalIndex']];
            }

            if (empty($descr)) {
                $descr = "Processor $index";
            }

            if (is_array($entry['cpmCore5min'])) {
                // This CPU has data per individual core
                foreach ($entry['cpmCore5min'] as $core_index => $core_usage) {
                    $processors[] = Processor::discover(
                        'cpm',
                        $this->getDeviceId(),
                        ".1.3.6.1.4.1.9.9.109.1.1.2.1.5.$index.$core_index",
                        "$index.$core_index",
                        "$descr: Core $core_index",
                        1,
                        $core_usage,
                        null,
                        $entry['cpmCPUTotalPhysicalIndex']
                    );
                }
            } else {
                $processors[] = Processor::discover(
                    'cpm',
                    $this->getDeviceId(),
                    $usage_oid,
                    $index,
                    $descr,
                    1,
                    $usage,
                    null,
                    $entry['cpmCPUTotalPhysicalIndex']
                );
            }
        }

        if (empty($processors)) {
            // fallback to old pre-12.0 OID
            $processors[] = Processor::discover(
                'ios',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.9.2.1.58.0', // OLD-CISCO-CPU-MIB::avgBusy5
                0
            );
        }

        // QFP processors (Forwarding Processors)
        $qfp_data = snmpwalk_group($this->getDeviceArray(), 'ceqfpUtilProcessingLoad', 'CISCO-ENTITY-QFP-MIB');

        foreach ($qfp_data as $entQfpPhysicalIndex => $entry) {
            /*
             * .2 OID suffix is for 1 min SMA ('oneMinute')
             * .3 OID suffix is for 5 min SMA ('fiveMinute')
             * Could be dynamically changed to appropriate value if config had pol interval value
             */
            $qfp_usage_oid = '.1.3.6.1.4.1.9.9.715.1.1.6.1.14.' . $entQfpPhysicalIndex . '.3';
            $qfp_usage = $entry['fiveMinute'];

            if ($entQfpPhysicalIndex) {
                $qfp_descr = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB')[$entQfpPhysicalIndex];
            }

            $processors[] = Processor::discover(
                'qfp',
                $this->getDeviceId(),
                $qfp_usage_oid,
                $entQfpPhysicalIndex . '.3',
                $qfp_descr ?? "QFP $entQfpPhysicalIndex",
                1,
                $qfp_usage,
                null,
                $entQfpPhysicalIndex
            );
        }

        return $processors;
    }

    public function discoverSlas()
    {
        $device = $this->getDeviceArray();

        $slas = collect();
        $data = snmp_walk($device, 'ciscoRttMonMIB.ciscoRttMonObjects.rttMonCtrl', '-Osq', '+CISCO-RTTMON-MIB');

        // Index the MIB information
        $sla_table = [];
        foreach (explode("\n", $data) as $index) {
            $key_val = explode(' ', $index, 2);
            if (count($key_val) != 2) {
                $key_val[] = '';
            }

            $key = $key_val[0];
            $value = $key_val[1];

            $prop_id = explode('.', $key);
            if ((count($prop_id) != 2) || ! ctype_digit($prop_id[1])) {
                continue;
            }

            $property = $prop_id[0];
            $id = intval($prop_id[1]);

            $sla_table[$id][$property] = trim($value);
        }

        foreach ($sla_table as $sla_nr => $sla_config) {
            $data = [
                'device_id' => $device['device_id'],
                'sla_nr'    => $sla_nr,
                'owner'     => $sla_config['rttMonCtrlAdminOwner'],
                'tag'       => $sla_config['rttMonCtrlAdminTag'],
                'rtt_type'  => $sla_config['rttMonCtrlAdminRttType'],
                'status'    => ($sla_config['rttMonCtrlAdminStatus'] == 'active') ? 1 : 0,
                'opstatus'  => ($sla_config['rttMonLatestRttOperSense'] == 'ok') ? 0 : 2,
                'deleted'   => 0,
            ];

            // Some fallbacks for when the tag is empty
            if (! $data['tag']) {
                switch ($data['rtt_type']) {
                    case 'http':
                        $data['tag'] = $sla_config['rttMonEchoAdminURL'];
                        break;

                    case 'dns':
                        $data['tag'] = $sla_config['rttMonEchoAdminTargetAddressString'];
                        break;

                    case 'echo':
                        $data['tag'] = IP::fromHexString($sla_config['rttMonEchoAdminTargetAddress'], true);
                        break;

                    case 'jitter':
                        if ($sla_config['rttMonEchoAdminCodecType'] != 'notApplicable') {
                            $codec_info = ' (' . $sla_config['rttMonEchoAdminCodecType'] . ' @ ' . preg_replace('/milliseconds/', 'ms', $sla_config['rttMonEchoAdminCodecInterval']) . ')';
                        } else {
                            $codec_info = '';
                        }
                        $data['tag'] = IP::fromHexString($sla_config['rttMonEchoAdminTargetAddress'], true) . ':' . $sla_config['rttMonEchoAdminTargetPort'] . $codec_info;
                        break;
                }//end switch
            }//end if
            $slas->push($data);
        }

        return $slas;
    }

    public function pollNac()
    {
        $nac = collect();

        $portAuthSessionEntry = snmpwalk_cache_oid($this->getDeviceArray(), 'cafSessionEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB');
        if (! empty($portAuthSessionEntry)) {
            $cafSessionMethodsInfoEntry = collect(snmpwalk_cache_oid($this->getDeviceArray(), 'cafSessionMethodsInfoEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB'))->mapWithKeys(function ($item, $key) {
                $key_parts = explode('.', $key);
                $key = implode('.', array_slice($key_parts, 0, 2)); // remove the auth method

                return [$key => ['method' => $key_parts[2], 'authc_status' => $item['cafSessionMethodState']]];
            });

            // cache port ifIndex -> port_id map
            $ifIndex_map = $this->getDevice()->ports()->pluck('port_id', 'ifIndex');

            // update the DB
            foreach ($portAuthSessionEntry as $index => $portAuthSessionEntryParameters) {
                [$ifIndex, $auth_id] = explode('.', str_replace("'", '', $index));
                $session_info = $cafSessionMethodsInfoEntry->get($ifIndex . '.' . $auth_id);
                $mac_address = strtolower(implode(array_map('zeropad', explode(':', $portAuthSessionEntryParameters['cafSessionClientMacAddress']))));

                $nac->put($mac_address, new PortsNac([
                    'port_id' => $ifIndex_map->get($ifIndex, 0),
                    'mac_address' => $mac_address,
                    'auth_id' => $auth_id,
                    'domain' => $portAuthSessionEntryParameters['cafSessionDomain'],
                    'username' => $portAuthSessionEntryParameters['cafSessionAuthUserName'],
                    'ip_address' => (string) IP::fromHexString($portAuthSessionEntryParameters['cafSessionClientAddress'], true),
                    'host_mode' => $portAuthSessionEntryParameters['cafSessionAuthHostMode'],
                    'authz_status' => $portAuthSessionEntryParameters['cafSessionStatus'],
                    'authz_by' => $portAuthSessionEntryParameters['cafSessionAuthorizedBy'],
                    'timeout' => $portAuthSessionEntryParameters['cafSessionTimeout'],
                    'time_left' => $portAuthSessionEntryParameters['cafSessionTimeLeft'],
                    'vlan' => $portAuthSessionEntryParameters['cafSessionAuthVlan'],
                    'authc_status' => $session_info['authc_status'],
                    'method' => $session_info['method'],
                ]));
            }
        }

        return $nac;
    }

    public function pollSlas($slas)
    {
        $device = $this->getDeviceArray();

        // Go get some data from the device.
        $rttMonLatestRttOperTable = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.42.1.2.10.1', 1);
        $rttMonLatestOper = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.42.1.5', 1);

        $uptime = snmp_get($device, 'sysUpTime.0', '-Otv');
        $time_offset = (time() - intval($uptime) / 100);

        foreach ($slas as $sla) {
            $sla_id = $sla['sla_id'];
            $sla_nr = $sla['sla_nr'];
            $rtt_type = $sla['rtt_type'];

            // Lets process each SLA
            $unixtime = intval(($rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.5'][$sla_nr] / 100 + $time_offset));
            $time = strftime('%Y-%m-%d %H:%M:%S', $unixtime);
            $update = [];

            // Use Nagios Status codes.
            $opstatus = $rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.2'][$sla_nr];
            if ($opstatus == 1) {
                $opstatus = 0;        // 0=Good
            } else {
                $opstatus = 2;        // 2=Critical
            }

            // Populating the update array means we need to update the DB.
            if ($opstatus != $sla['opstatus']) {
                $update['opstatus'] = $opstatus;
            }

            $rtt = $rttMonLatestRttOperTable['1.3.6.1.4.1.9.9.42.1.2.10.1.1'][$sla_nr];
            echo 'SLA ' . $sla_nr . ': ' . $rtt_type . ' ' . $sla['owner'] . ' ' . $sla['tag'] . '... ' . $rtt . 'ms at ' . $time . '\n';

            $fields = [
                'rtt' => $rtt,
            ];

            // The base RRD
            $rrd_name = ['sla', $sla['sla_nr']];
            $rrd_def = RrdDefinition::make()->addDataset('rtt', 'GAUGE', 0, 300000);
            $tags = compact('sla_nr', 'rrd_name', 'rrd_def');
            data_update($device, 'sla', $tags, $fields);

            // Let's gather some per-type fields.
            switch ($rtt_type) {
                case 'jitter':
                    $jitter = [
                        'PacketLossSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.26'][$sla_nr],
                        'PacketLossDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.27'][$sla_nr],
                        'PacketOutOfSequence' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.28'][$sla_nr],
                        'PacketMIA' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.29'][$sla_nr],
                        'PacketLateArrival' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.30'][$sla_nr],
                        'MOS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.42'][$sla_nr] / 100,
                        'ICPIF' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.43'][$sla_nr],
                        'OWAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.49'][$sla_nr],
                        'OWAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.50'][$sla_nr],
                        'AvgSDJ' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.47'][$sla_nr],
                        'AvgDSJ' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.2.1.48'][$sla_nr],
                    ];
                    $rrd_name = ['sla', $sla_nr, $rtt_type];
                    $rrd_def = RrdDefinition::make()
                        ->addDataset('PacketLossSD', 'GAUGE', 0)
                        ->addDataset('PacketLossDS', 'GAUGE', 0)
                        ->addDataset('PacketOutOfSequence', 'GAUGE', 0)
                        ->addDataset('PacketMIA', 'GAUGE', 0)
                        ->addDataset('PacketLateArrival', 'GAUGE', 0)
                        ->addDataset('MOS', 'GAUGE', 0)
                        ->addDataset('ICPIF', 'GAUGE', 0)
                        ->addDataset('OWAvgSD', 'GAUGE', 0)
                        ->addDataset('OWAvgDS', 'GAUGE', 0)
                        ->addDataset('AvgSDJ', 'GAUGE', 0)
                        ->addDataset('AvgDSJ', 'GAUGE', 0);
                    $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                    data_update($device, 'sla', $tags, $jitter);
                    $fields = array_merge($fields, $jitter);
                    break;
                case 'icmpjitter':
                    $icmpjitter = [
                        'PacketLoss' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.26'][$sla_nr],
                        'PacketOosSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.28'][$sla_nr],
                        'PacketOosDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.29'][$sla_nr],
                        'PacketLateArrival' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.32'][$sla_nr],
                        'JitterAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.45'][$sla_nr],
                        'JitterAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.46'][$sla_nr],
                        'LatencyOWAvgSD' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.47'][$sla_nr],
                        'LatencyOWAvgDS' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.48'][$sla_nr],
                        'JitterIAJOut' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.49'][$sla_nr],
                        'JitterIAJIn' => $rttMonLatestOper['1.3.6.1.4.1.9.9.42.1.5.4.1.50'][$sla_nr],
                    ];
                    $rrd_name = ['sla', $sla_nr, $rtt_type];
                    $rrd_def = RrdDefinition::make()
                        ->addDataset('PacketLoss', 'GAUGE', 0)
                        ->addDataset('PacketOosSD', 'GAUGE', 0)
                        ->addDataset('PacketOosDS', 'GAUGE', 0)
                        ->addDataset('PacketLateArrival', 'GAUGE', 0)
                        ->addDataset('JitterAvgSD', 'GAUGE', 0)
                        ->addDataset('JitterAvgDS', 'GAUGE', 0)
                        ->addDataset('LatencyOWAvgSD', 'GAUGE', 0)
                        ->addDataset('LatencyOWAvgDS', 'GAUGE', 0)
                        ->addDataset('JitterIAJOut', 'GAUGE', 0)
                        ->addDataset('JitterIAJIn', 'GAUGE', 0);
                    $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                    data_update($device, 'sla', $tags, $icmpjitter);
                    $fields = array_merge($fields, $icmpjitter);
                    break;
            }

            d_echo('The following datasources were collected for #' . $sla['sla_nr'] . ":\n");
            d_echo($fields);

            // Update the DB if necessary
            if (count($update) > 0) {
                Sla::where('sla_id', $sla_id)
                ->update($update);
            }
        }
    }

    protected function getMainSerial()
    {
        $serial_output = snmp_get_multi($this->getDeviceArray(), ['entPhysicalSerialNum.1', 'entPhysicalSerialNum.1001'], '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');
//        $serial_output = snmp_getnext($this->getDevice(), 'entPhysicalSerialNum', '-OQUs', 'ENTITY-MIB:OLD-CISCO-CHASSIS-MIB');

        if (! empty($serial_output[1]['entPhysicalSerialNum'])) {
            return $serial_output[1]['entPhysicalSerialNum'];
        } elseif (! empty($serial_output[1000]['entPhysicalSerialNum'])) {
            return $serial_output[1000]['entPhysicalSerialNum'];
        } elseif (! empty($serial_output[1001]['entPhysicalSerialNum'])) {
            return $serial_output[1001]['entPhysicalSerialNum'];
        }

        return null;
    }
}
