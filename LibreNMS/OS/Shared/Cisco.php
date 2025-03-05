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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 * @copyright  2018 Jose Augusto Cardoso
 */

namespace LibreNMS\OS\Shared;

use App\Facades\PortCache;
use App\Models\Component;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Mempool;
use App\Models\PortsNac;
use App\Models\Qos;
use App\Models\Sla;
use App\Models\Storage;
use App\Models\Transceiver;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Discovery\QosDiscovery;
use LibreNMS\Interfaces\Discovery\SlaDiscovery;
use LibreNMS\Interfaces\Discovery\StorageDiscovery;
use LibreNMS\Interfaces\Discovery\StpInstanceDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\Interfaces\Polling\QosPolling;
use LibreNMS\Interfaces\Polling\SlaPolling;
use LibreNMS\OS;
use LibreNMS\OS\Traits\YamlOSDiscovery;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use SnmpQuery;

class Cisco extends OS implements
    OSDiscovery,
    SlaDiscovery,
    StpInstanceDiscovery,
    ProcessorDiscovery,
    QosDiscovery,
    MempoolsDiscovery,
    NacPolling,
    QosPolling,
    SlaPolling,
    StorageDiscovery,
    TransceiverDiscovery
{
    use YamlOSDiscovery {
        YamlOSDiscovery::discoverOS as discoverYamlOS;
    }
    use OS\Traits\EntityMib {
        OS\Traits\EntityMib::discoverEntityPhysical as discoverBaseEntityPhysical;
    }

    private Collection $qosIdxToParent;
    protected ?string $entityVendorTypeMib = 'CISCO-ENTITY-VENDORTYPE-OID-MIB';

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
        } elseif ((empty($hardware) || preg_match('/Virtual Stack/', $hardware)) && ! empty($data[1000]['entPhysicalModelName'])) {
            $hardware = $data[1000]['entPhysicalModelName'];
        } elseif (empty($hardware) && ! empty($data[1000]['entPhysicalContainedIn'])) {
            $hardware = $data[$data[1000]['entPhysicalContainedIn']]['entPhysicalName'];
        } elseif ((preg_match('/stack/i', $hardware ?? '') || empty($hardware)) && ! empty($data[1001]['entPhysicalModelName'])) {
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

        $mempools = new Collection();
        $cemp = snmpwalk_cache_multi_oid($this->getDeviceArray(), 'cempMemPoolTable', [], 'CISCO-ENHANCED-MEMPOOL-MIB');

        foreach (Arr::wrap($cemp) as $index => $entry) {
            if (is_numeric($entry['cempMemPoolUsed']) && $entry['cempMemPoolValid'] == 'true') {
                [$entPhysicalIndex] = explode('.', $index);
                $entPhysicalName = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
                $descr = ucwords((isset($entPhysicalName[$entPhysicalIndex]) ? "{$entPhysicalName[$entPhysicalIndex]} - " : '') . $entry['cempMemPoolName']);
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
            if (isset($entry['cpmCPUMemoryFree']) && is_numeric($entry['cpmCPUMemoryFree'])) {
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

    public function discoverEntityPhysical(): Collection
    {
        $inventory = $this->discoverBaseEntityPhysical();

        $os = $this->getDevice()->os;

        // discover cellular device info
        if ($os == 'ios' or $os == 'iosxe') {
            $cellData = SnmpQuery::hideMib()->walk('CISCO-WAN-3G-MIB::c3gGsmIdentityTable');
            $baseIndex = $inventory->max('entPhysicalIndex'); // maintain compatability with buggy old code

            foreach ($cellData->table(1) as $index => $entry) {
                if (isset($entry['c3gImsi'])) {
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => ++$baseIndex,
                        'entPhysicalDescr' => $entry['c3gImsi'],
                        'entPhysicalVendorType' => 'sim',
                        'entPhysicalContainedIn' => $index,
                        'entPhysicalClass' => 'module',
                        'entPhysicalParentRelPos' => '-1',
                        'entPhysicalName' => 'sim',
                        'entPhysicalModelName' => 'IMSI',
                        'entPhysicalIsFRU' => 'true',
                    ]));
                }

                if (isset($entry['c3gImei'])) {
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => ++$baseIndex,
                        'entPhysicalDescr' => $entry['c3gImei'],
                        'entPhysicalVendorType' => 'modem',
                        'entPhysicalContainedIn' => $index,
                        'entPhysicalClass' => 'module',
                        'entPhysicalParentRelPos' => '-1',
                        'entPhysicalName' => 'modem',
                        'entPhysicalModelName' => 'IMEI',
                        'entPhysicalIsFRU' => 'false',
                    ]));
                }

                if (isset($entry['c3gIccId'])) {
                    $inventory->push(new EntPhysical([
                        'entPhysicalIndex' => ++$baseIndex,
                        'entPhysicalDescr' => $entry['c3gIccId'],
                        'entPhysicalVendorType' => 'sim',
                        'entPhysicalContainedIn' => $index,
                        'entPhysicalClass' => 'module',
                        'entPhysicalParentRelPos' => '-1',
                        'entPhysicalName' => 'sim',
                        'entPhysicalModelName' => 'ICCID',
                        'entPhysicalIsFRU' => 'true',
                    ]));
                }
            }
        }

        return $inventory;
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
                $descr = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB')[$entry['cpmCPUTotalPhysicalIndex']] ?? null;
            }

            if (empty($descr)) {
                $descr = "Processor $index";
            }

            if (isset($entry['cpmCore5min']) && is_array($entry['cpmCore5min'])) {
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
            $qfp_usage = $entry['fiveMinute'] ?? null;

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

    public function discoverSlas(): Collection
    {
        $slas = new Collection();

        $sla_data = snmpwalk_cache_oid($this->getDeviceArray(), 'rttMonCtrl', [], 'CISCO-RTTMON-MIB', null, '-OQUsx');

        if (! empty($sla_data)) {
            $sla_data = snmpwalk_cache_oid($this->getDeviceArray(), 'rttMonLatestRttOperCompletionTime', $sla_data, 'CISCO-RTTMON-MIB');
        }

        foreach ($sla_data as $index => $sla_config) {
            if (empty($sla_config['rttMonCtrlAdminRttType'])) {
                continue; // skip garbage entries
            }

            $slas->push(new Sla([
                'sla_nr' => $index,
                'owner' => $sla_config['rttMonCtrlAdminOwner'] ?? '',
                'tag' => $this->getSlaTag($sla_config),
                'rtt_type' => $sla_config['rttMonCtrlAdminRttType'],
                'rtt' => $sla_config['rttMonLatestRttOperCompletionTime'] ?? null,
                'status' => ($sla_config['rttMonCtrlAdminStatus'] == 'active') ? 1 : 0,
                'opstatus' => ($sla_config['rttMonLatestRttOperSense'] == 'ok') ? 0 : 2,
            ]));
        }

        return $slas;
    }

    private function getSlaTag($data): string
    {
        if (! empty($data['rttMonCtrlAdminTag'])) {
            return $data['rttMonCtrlAdminTag'];
        }

        switch ($data['rttMonCtrlAdminRttType']) {
            case 'http':
                return $data['rttMonEchoAdminURL'] ?? '';
            case 'dns':
                return $data['rttMonEchoAdminTargetAddressString'] ?? '';
            case 'echo':
                return IP::fromHexString($data['rttMonEchoAdminTargetAddress'], true) ?? '';
            case 'jitter':
                $tag = IP::fromHexString($data['rttMonEchoAdminTargetAddress'], true) . ':' . $data['rttMonEchoAdminTargetPort'];
                if (isset($data['rttMonEchoAdminCodecType']) && $data['rttMonEchoAdminCodecType'] != 'notApplicable') {
                    $tag .= ' (' . $data['rttMonEchoAdminCodecType'] . ' @ ' . $data['rttMonEchoAdminCodecInterval'] . 'ms)';
                }

                return $tag;
            default:
                return '';
        }
    }

    public function discoverStorage(): Collection
    {
        $devices = SnmpQuery::walk('CISCO-FLASH-MIB::ciscoFlashDeviceName')->pluck();

        return SnmpQuery::walk('CISCO-FLASH-MIB::ciscoFlashPartitionTable')
            ->mapTable(function ($data, $ciscoFlashDeviceIndex, $ciscoFlashPartitionIndex) use ($devices) {
                $index = "$ciscoFlashDeviceIndex.$ciscoFlashPartitionIndex";
                $size = $data['CISCO-FLASH-MIB::ciscoFlashPartitionSize'] == 4294967295
                    ? $data['CISCO-FLASH-MIB::ciscoFlashPartitionSizeExtended']
                    : $data['CISCO-FLASH-MIB::ciscoFlashPartitionSize'];

                if ($data['CISCO-FLASH-MIB::ciscoFlashPartitionFreeSpace'] == 4294967295) {
                    $free = $data['CISCO-FLASH-MIB::ciscoFlashPartitionFreeSpaceExtended'];
                    $free_oid = ".1.3.6.1.4.1.9.9.10.1.1.4.1.1.14.$index";
                } else {
                    $free = $data['CISCO-FLASH-MIB::ciscoFlashPartitionFreeSpace'];
                    $free_oid = ".1.3.6.1.4.1.9.9.10.1.1.4.1.1.5.$index";
                }

                $descr = $devices[$ciscoFlashDeviceIndex] ?? '';
                if ($descr != $data['CISCO-FLASH-MIB::ciscoFlashPartitionName']) {
                    $descr .= '(' . $data['CISCO-FLASH-MIB::ciscoFlashPartitionName'] . ')';
                }
                $descr .= ':';

                return (new Storage([
                    'type' => 'cisco-flash',
                    'storage_descr' => $descr,
                    'storage_index' => $index,
                    'storage_type' => 'FlashMemory',
                    'storage_free_oid' => $free_oid,
                    'storage_units' => 1,
                ]))->fillUsage(total: $size, free: $free);
            });
    }

    public function pollNac()
    {
        $nac = new Collection();

        $portAuthSessionEntry = snmpwalk_cache_oid($this->getDeviceArray(), 'cafSessionEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB', null, '-OQUsx');
        if (! empty($portAuthSessionEntry)) {
            $cafSessionMethodsInfoEntry = collect(snmpwalk_cache_oid($this->getDeviceArray(), 'cafSessionMethodsInfoEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB', null, '-OQUsx'))->mapWithKeys(function ($item, $key) {
                $key_parts = explode('.', $key);
                $key = implode('.', array_slice($key_parts, 0, 2)); // remove the auth method

                return [$key => ['method' => $key_parts[2], 'authc_status' => $item['cafSessionMethodState']]];
            });

            // update the DB
            foreach ($portAuthSessionEntry as $index => $portAuthSessionEntryParameters) {
                [$ifIndex, $auth_id] = explode('.', str_replace("'", '', $index));
                $session_info = $cafSessionMethodsInfoEntry->get($ifIndex . '.' . $auth_id);
                $mac_address = Mac::parse($portAuthSessionEntryParameters['cafSessionClientMacAddress'] ?? '')->hex();

                $nac->put($mac_address, new PortsNac([
                    'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                    'mac_address' => $mac_address,
                    'auth_id' => $auth_id,
                    'domain' => $portAuthSessionEntryParameters['cafSessionDomain'] ?? '',
                    'username' => $portAuthSessionEntryParameters['cafSessionAuthUserName'] ?? '',
                    'ip_address' => (string) IP::fromHexString($portAuthSessionEntryParameters['cafSessionClientAddress'] ?? '', true),
                    'host_mode' => $portAuthSessionEntryParameters['cafSessionAuthHostMode'] ?? '',
                    'authz_status' => $portAuthSessionEntryParameters['cafSessionStatus'] ?? '',
                    'authz_by' => $portAuthSessionEntryParameters['cafSessionAuthorizedBy'] ?? '',
                    'timeout' => $portAuthSessionEntryParameters['cafSessionTimeout'] ?? '',
                    'time_left' => $portAuthSessionEntryParameters['cafSessionTimeLeft'] ?? null,
                    'vlan' => $portAuthSessionEntryParameters['cafSessionAuthVlan'] ?? null,
                    'authc_status' => $session_info['authc_status'] ?? '',
                    'method' => $session_info['method'] ?? '',
                ]));
            }
        }

        return $nac;
    }

    public function pollSlas($slas): void
    {
        $device = $this->getDeviceArray();

        $data = snmpwalk_group($device, 'rttMonLatestRttOperTable', 'CISCO-RTTMON-MIB');
        $data = snmpwalk_group($device, 'rttMonLatestOper', 'CISCO-RTTMON-MIB', 1, $data);
        $data = snmpwalk_group($device, 'rttMonEchoAdminNumPackets', 'CISCO-RTTMON-MIB', 1, $data);
        $data = snmpwalk_group($device, 'rttMonLatestIcmpJitterOperTable', 'CISCO-RTTMON-ICMP-MIB', 1, $data);

        $time_offset = time() - $this->getDevice()->uptime;

        foreach ($slas as $sla) {
            $sla_id = $sla->sla_id;
            $sla_nr = $sla->sla_nr;
            $rtt_type = $sla->rtt_type;

            // Lets process each SLA
            if (! isset($data[$sla_nr]['rttMonLatestRttOperTime'])) {
                continue;
            }
            $unixtime = intval($data[$sla_nr]['rttMonLatestRttOperTime'] / 100 + $time_offset);
            $time = date('Y-m-d H:i:s', $unixtime);

            // Save data
            $sla->rtt = $data[$sla_nr]['rttMonLatestRttOperCompletionTime'];
            // Use Nagios Status codes. 0: Good, 2: Critical
            $sla->opstatus = $data[$sla_nr]['rttMonLatestRttOperSense'] == 1 ? 0 : 2;

            Log::info('SLA ' . $sla_nr . ': ' . $rtt_type . ' ' . $sla['owner'] . ' ' . $sla['tag'] . '... ' . $sla->rtt . 'ms at ' . $time);

            $collected = ['rtt' => $sla->rtt];

            // Let's gather some per-type fields.
            switch ($rtt_type) {
                case 'jitter':
                    $jitter = [
                        'PacketLossSD' => $data[$sla_nr]['rttMonLatestJitterOperPacketLossSD'],
                        'PacketLossDS' => $data[$sla_nr]['rttMonLatestJitterOperPacketLossDS'],
                        'PacketOutOfSequence' => $data[$sla_nr]['rttMonLatestJitterOperPacketOutOfSequence'] ?? null,
                        'PacketMIA' => $data[$sla_nr]['rttMonLatestJitterOperPacketMIA'] ?? null,
                        'PacketLateArrival' => $data[$sla_nr]['rttMonLatestJitterOperPacketLateArrival'],
                        'MOS' => isset($data[$sla_nr]['rttMonLatestJitterOperMOS']) ? intval($data[$sla_nr]['rttMonLatestJitterOperMOS']) / 100 : null,
                        'ICPIF' => $data[$sla_nr]['rttMonLatestJitterOperICPIF'] ?? null,
                        'OWAvgSD' => $data[$sla_nr]['rttMonLatestJitterOperOWAvgSD'] ?? null,
                        'OWAvgDS' => $data[$sla_nr]['rttMonLatestJitterOperOWAvgDS'] ?? null,
                        'AvgSDJ' => $data[$sla_nr]['rttMonLatestJitterOperAvgSDJ'] ?? null,
                        'AvgDSJ' => $data[$sla_nr]['rttMonLatestJitterOperAvgDSJ'] ?? null,
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
                    app('Datastore')->put($device, 'sla', $tags, $jitter);
                    $collected = array_merge($collected, $jitter);
                    // Additional rrd for total number packet in sla
                    $numPackets = [
                        'NumPackets' => $data[$sla_nr]['rttMonEchoAdminNumPackets'],
                    ];
                    $rrd_name = ['sla', $sla_nr, 'NumPackets'];
                    $rrd_def = RrdDefinition::make()
                        ->addDataset('NumPackets', 'GAUGE', 0);
                    $tags = compact('rrd_name', 'rrd_def', 'sla_nr', 'rtt_type');
                    app('Datastore')->put($device, 'sla', $tags, $numPackets);
                    $collected = array_merge($collected, $numPackets);
                    break;
                case 'icmpjitter':
                    // icmpJitter data is placed at different locations in MIB tree, possibly based on IOS version
                    // First look for values as originally implemented in lnms (from CISCO-RTTMON-MIB), then look for OIDs defined in CISCO-RTTMON-ICMP-MIB
                    // This MIGHT mix values if a device presents some data from one and some from the other

                    $icmpjitter = [
                        'PacketLoss' => $data[$sla_nr]['rttMonLatestJitterOperPacketLossSD'] ?? $data[$sla_nr]['rttMonLatestIcmpJitterPktLoss'],
                        'PacketOosSD' => $data[$sla_nr]['rttMonLatestJitterOperPacketOutOfSequence'] ?? $data[$sla_nr]['rttMonLatestIcmpJPktOutSeqBoth'],
                        // No equivalent found in CISCO-RTTMON-ICMP-MIB, return null
                        'PacketOosDS' => $data[$sla_nr]['rttMonLatestJitterOperPacketMIA'] ?? null,
                        'PacketLateArrival' => $data[$sla_nr]['rttMonLatestJitterOperPacketLateArrival'] ?? $data[$sla_nr]['rttMonLatestIcmpJitterPktLateA'],
                        'JitterAvgSD' => $data[$sla_nr]['rttMonLatestJitterOperAvgSDJ'] ?? $data[$sla_nr]['rttMonLatestIcmpJitterAvgSDJ'],
                        'JitterAvgDS' => $data[$sla_nr]['rttMonLatestJitterOperAvgDSJ'] ?? $data[$sla_nr]['rttMonLatestIcmpJitterAvgDSJ'],
                        'LatencyOWAvgSD' => $data[$sla_nr]['rttMonLatestJitterOperOWAvgSD'] ?? $data[$sla_nr]['rttMonLatestIcmpJitterOWAvgSD'],
                        'LatencyOWAvgDS' => $data[$sla_nr]['rttMonLatestJitterOperOWAvgDS'] ?? $data[$sla_nr]['rttMonLatestIcmpJitterOWAvgDS'],
                        'JitterIAJOut' => $data[$sla_nr]['rttMonLatestJitterOperIAJOut'] ?? $data[$sla_nr]['rttMonLatestIcmpJitterIAJOut'],
                        'JitterIAJIn' => $data[$sla_nr]['rttMonLatestJitterOperIAJIn'] ?? $data[$sla_nr]['rttMonLatestIcmpJitterIAJIn'],
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
                    app('Datastore')->put($device, 'sla', $tags, $icmpjitter);
                    $collected = array_merge($collected, $icmpjitter);
                    break;
            }

            d_echo('The following datasources were collected for #' . $sla['sla_nr'] . ":\n");
            d_echo($collected);
        }
    }

    public function discoverStpInstances(?string $vlan = null): Collection
    {
        $vlans = $this->getDevice()->vlans;
        $instances = new Collection;

        //get Cisco stpxSpanningTreeType
        $stpxSpanningTreeType = SnmpQuery::enumStrings()->hideMib()->get('CISCO-STP-EXTENSIONS-MIB::stpxSpanningTreeType.0')->value();

        // attempt to discover context based vlan instances
        foreach ($vlans->isEmpty() ? [null] : $vlans as $vlan) {
            $vlan = (empty($vlan->vlan_vlan) || $vlan->vlan_vlan == '1') ? null : (string) $vlan->vlan_vlan;
            $instance = parent::discoverStpInstances($vlan);
            if ($instance[0]->protocolSpecification == 'unknown') {
                $instance[0]->protocolSpecification = $stpxSpanningTreeType;
            }
            $instances = $instances->merge($instance);
        }

        return $instances;
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

    public function discoverTransceivers(): Collection
    {
        // use data collected by entPhysical module if available
        $arrayOfContainers = ['cevContainerSFP', 'cevContainerGbic', 'cevContainer10GigBasePort', 'cevContainerTransceiver', 'cevContainerXFP', 'cevContainer40GigBasePort', 'cevContainerCFP', 'cevContainerCXP', 'cevContainerCPAK', 'cevContainerNCS4KSFP', 'cevContainerQSFP28SR', 'cevContainerQSFP28LR', 'cevContainerQSFP28CR', 'cevContainerQSFP28AOC', 'cevContainerQSFP28CWDM', 'cevContainerNonCiscoQSFP28SR', 'cevContainerNonCiscoQSFP28LR', 'cevContainerNonCiscoQSFP28CR', 'cevContainerNonCiscoQSFP28AOC', 'cevContainerNonCiscoQSFP28CWDM', 'cevContainerQSFPDD'];

        $dbSfpCages = $this->getDevice()->entityPhysical()->whereIn('entPhysicalVendorType', $arrayOfContainers)->pluck('ifIndex', 'entPhysicalIndex');
        if ($dbSfpCages->isNotEmpty()) {
            $data = $this->getDevice()->entityPhysical()->whereIn('entPhysicalContainedIn', $dbSfpCages->keys())->get()->map(function ($ent) use ($dbSfpCages) {
                if (empty($ent->ifIndex) && $dbSfpCages->has($ent->entPhysicalContainedIn)) {
                    $ent->ifIndex = $dbSfpCages->get($ent->entPhysicalContainedIn);
                    if (empty($ent->ifIndex)) {
                        // Lets try to find the 1st subentity with an ifIndex below this one and use it. Some (most?) ISR and ASR on IOSXE at least are behaving like this.
                        $ent->ifIndex = $this->getDevice()->entityPhysical()->where('entPhysicalContainedIn', '=', $ent->entPhysicalIndex)->whereNotNull('ifIndex')->first()->ifIndex;
                    }
                }

                return $ent;
            })->keyBy('entPhysicalIndex');
        } else {
            // fetch data via snmp
            $snmpData = SnmpQuery::cache()->hideMib()->mibs(['CISCO-ENTITY-VENDORTYPE-OID-MIB'])->walk('ENTITY-MIB::entPhysicalTable')->table(1);
            if (empty($snmpData)) {
                return new Collection;
            }

            $snmpData = collect(SnmpQuery::hideMib()->mibs(['IF-MIB'])->walk('ENTITY-MIB::entAliasMappingIdentifier')->table(1, $snmpData));
            $sfpCages = $snmpData->filter(fn ($ent) => isset($ent['entPhysicalVendorType']) && in_array($ent['entPhysicalVendorType'], $arrayOfContainers));
            $dataFilter = $snmpData->filter(fn ($ent) => $sfpCages->has($ent['entPhysicalContainedIn'] ?? null));
            $data = $dataFilter->map(function ($e, $e_index) use ($snmpData) {
                $e['entPhysicalIndex'] = $e_index;
                if (isset($e['entAliasMappingIdentifier'][0])) {
                    $e['ifIndex'] = preg_replace('/^.*ifIndex[.[](\d+).*$/', '$1', $e['entAliasMappingIdentifier'][0]);
                } else {
                    // Lets try to find the 1st subentity with an ifIndex below this one and use it. Some (most?) ISR and ASR on IOSXE at least are behaving like this.
                    $sibling = $snmpData->filter(fn ($ent, $ent_index) => ($ent['entPhysicalContainedIn'] == $e_index) && isset($ent['entAliasMappingIdentifier'][0]))->first();
                    // If we found one, let's use this ifindex
                    if ($sibling) {
                        $ifIndexTmp = $sibling['entAliasMappingIdentifier'][0];
                        if (isset($ifIndexTmp)) {
                            $e['ifIndex'] = preg_replace('/^.*ifIndex[.[](\d+).*$/', '$1', $ifIndexTmp);
                        }
                    }
                }

                return $e;
            });
        }

        return $data->map(function ($ent, $index) {
            $ifIndex = $ent['ifIndex'] ?? null;

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $index,
                'type' => $ent['entPhysicalDescr'] ?? null,
                'vendor' => $ent['entPhysicalMfgName'] ?? null,
                'revision' => $ent['entPhysicalHardwareRev'] ?? null,
                'model' => $ent['entPhysicalModelName'] ?? null,
                'serial' => $ent['entPhysicalSerialNum'] ?? null,
                'entity_physical_index' => $ent['entPhysicalIndex'],
            ]);
        });
    }

    public function discoverQos(): Collection
    {
        $this->qosIdxToParent = new Collection();
        $qos = new Collection();

        // SNMP lookup tables
        $servicePolicies = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosServicePolicyTable')->table(1);
        $policyMaps = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosPolicyMapCfgTable')->table(1);
        $classMaps = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosCMCfgTable')->table(1);
        $matchStatements = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosMatchStmtCfgTable')->table(1);
        $qosObjects = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosObjectsTable')->table(2);

        // Iterate over a 2 level table with keys of the policy ID, then the object ID
        foreach ($qosObjects as $policyId => $spObjects) {
            // Policy level settings
            $direction = $servicePolicies[$policyId]['cbQosPolicyDirection'];

            foreach ($spObjects as $objectId => $qosObject) {
                $qosObjectIndex = $qosObject['cbQosConfigIndex'];
                $type = $qosObject['cbQosObjectsType'];
                $parent = $qosObject['cbQosParentObjectsIndex'];
                $snmpIndex = "$policyId.$objectId";

                $tooltip = null;
                if ($type == 1) {
                    // Policy map
                    $dbtype = 'cisco_cbqos_policymap';
                    // Type 1 is not polled, but we need to set RRD ID to somethign unique because it's part of the DB key
                    $rrd_id = 'cbqos-policymap-' . $policyId . '-' . $objectId;
                    $pm = $policyMaps[$qosObjectIndex] ?? [];
                    $title = implode(' - ', array_filter(array_intersect_key($pm, ['cbQosPolicyMapName' => true, 'cbQosPolicyMapDesc' => true])));
                } elseif ($type == 2) {
                    // Class Map
                    $dbtype = 'cisco_cbqos_classmap';
                    // RRD name matches the original cbqos module
                    $rrd_id = 'port-' . $servicePolicies[$policyId]['cbQosIfIndex'] . '-cbqos-' . $policyId . '-' . $objectId;
                    $cm = $classMaps[$qosObjectIndex] ?? [];
                    $title = implode(' - ', array_filter(array_intersect_key($cm, ['cbQosCMName' => true, 'cbQosCMDesc' => true])));

                    // Fill in the match type
                    if ($cm['cbQosCMInfo'] == 2) {
                        $tooltip = 'Match-All:';
                    } elseif ($cm['cbQosCMInfo'] == 3) {
                        $tooltip = 'Match-Any:';
                    } else {
                        $tooltip = 'None';
                    }

                    // Then find the match statements
                    $statements = [];
                    foreach ($spObjects as $sqObject) {
                        // Find child objects (we are the parent) that are type 3 (match statements)
                        if ($sqObject['cbQosParentObjectsIndex'] == $objectId && $sqObject['cbQosObjectsType'] == 3) {
                            $statements[] = $matchStatements[$sqObject['cbQosConfigIndex']]['cbQosMatchStmtName'];
                        }
                    }

                    if (count($statements) > 0) {
                        $tooltip .= "\n - " . implode("\n - ", $statements);
                    }
                } else {
                    // Other types are not relevant
                    continue;
                }

                d_echo("\nIndex: " . $qosObjectIndex . "\n");
                d_echo('  SNMP Index: ' . $snmpIndex . "\n");
                d_echo('  Title     : ' . $title . "\n");
                d_echo('  Direction : ' . $direction . "\n");
                d_echo('  Parent    : ' . $policyId . '.' . $parent . "\n");

                // Our parent's SNMP index will share the same policyId
                $this->qosIdxToParent->put($snmpIndex, "$policyId.$parent");

                $qos->push(new Qos([
                    'device_id' => $this->getDeviceId(),
                    'port_id' => $parent ? null : PortCache::getIdFromIfIndex($servicePolicies[$policyId]['cbQosIfIndex'], $this->getDevice()),
                    'type' => $dbtype,
                    'title' => $title,
                    'tooltip' => $tooltip,
                    'rrd_id' => $rrd_id,
                    'snmp_idx' => $snmpIndex,
                    'ingress' => $direction == 1 ? 1 : 0,
                    'egress' => $direction == 2 ? 1 : 0,
                ]));
            }
        }

        // Clean up legacy component based config
        $oldConfig = Component::where('type', 'Cisco-CBQOS')->get();
        if ($oldConfig->count()) {
            foreach ($oldConfig as $oc) {
                $oc->delete();
            }
        }

        return $qos;
    }

    public function setQosParents($qos)
    {
        $qos->each(function (Qos $thisQos, int $key) use ($qos) {
            $parent_idx = $this->qosIdxToParent->get($thisQos->snmp_idx);

            if ($parent_idx) {
                $parent = $qos->where('snmp_idx', $parent_idx)->first();

                if ($parent) {
                    $parent_id = $parent->qos_id;
                } else {
                    $parent_id = null;
                }
            } else {
                $parent_id = null;
            }

            $thisQos->parent_id = $parent_id;
            $thisQos->save();
        });
    }

    public function pollQos($qos)
    {
        $poll_time = time();
        $preBytes = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosCMPrePolicyByte64')->table(2);
        $postBytes = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosCMPostPolicyByte64')->table(2);
        $dropBytes = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosCMDropByte64')->table(2);
        $bufferDrops = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosCMNoBufDropPkt64')->table(2);
        $prePackets = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosCMPrePolicyPkt64')->table(2);
        $dropPackets = SnmpQuery::hideMib()->walk('CISCO-CLASS-BASED-QOS-MIB::cbQosCMDropPkt64')->table(2);

        foreach ($qos as $thisQos) {
            if ($thisQos->type == 'cisco_cbqos_classmap') {
                $snmp_parts = explode('.', $thisQos->snmp_idx);

                // Ignore changes to QoS maps between discovery runs
                if (! array_key_exists($snmp_parts[0], $preBytes) || ! array_key_exists($snmp_parts[1], $preBytes[$snmp_parts[0]])) {
                    d_echo('Cisco CBQoS ' . $thisQos->title . ' not processed because SNMP did not return any data');

                    // Null out all values so we get a break in the graph
                    $thisQos->last_polled = $poll_time;
                    $thisQos->last_bytes_in = null;
                    $thisQos->last_bytes_out = null;
                    $thisQos->last_bytes_drop_in = null;
                    $thisQos->last_bytes_drop_out = null;
                    $thisQos->last_packets_in = null;
                    $thisQos->last_packets_out = null;
                    $thisQos->last_packets_drop_in = null;
                    $thisQos->last_packets_drop_out = null;
                    $thisQos->poll_data['postbytes'] = null;
                    $thisQos->poll_data['bufferdrops'] = null;

                    continue;
                }

                // Values ony saved to RRD
                $thisQos->poll_data['postbytes'] = $postBytes ? $postBytes[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMPostPolicyByte64'] : null;
                $thisQos->poll_data['bufferdrops'] = $bufferDrops ? $bufferDrops[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMNoBufDropPkt64'] : null;

                // Cisco CBQoS is one directional
                if ($thisQos->ingress) {
                    $thisQos->last_polled = $poll_time;
                    $thisQos->last_bytes_in = $preBytes ? $preBytes[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMPrePolicyByte64'] : null;
                    $thisQos->last_bytes_out = null;
                    $thisQos->last_bytes_drop_in = $dropBytes ? $dropBytes[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMDropByte64'] : null;
                    $thisQos->last_bytes_drop_out = null;
                    $thisQos->last_packets_in = $prePackets ? $prePackets[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMPrePolicyPkt64'] : null;
                    $thisQos->last_packets_out = null;
                    $thisQos->last_packets_drop_in = $dropPackets ? $dropPackets[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMDropPkt64'] : null;
                    $thisQos->last_packets_drop_out = null;
                } elseif ($thisQos->egress) {
                    $thisQos->last_polled = $poll_time;
                    $thisQos->last_bytes_in = null;
                    $thisQos->last_bytes_out = $preBytes ? $preBytes[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMPrePolicyByte64'] : null;
                    $thisQos->last_bytes_drop_in = null;
                    $thisQos->last_bytes_drop_out = $dropBytes ? $dropBytes[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMDropByte64'] : null;
                    $thisQos->last_packets_in = null;
                    $thisQos->last_packets_out = $prePackets ? $prePackets[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMPrePolicyPkt64'] : null;
                    $thisQos->last_packets_drop_in = null;
                    $thisQos->last_packets_drop_out = $dropPackets ? $dropPackets[$snmp_parts[0]][$snmp_parts[1]]['cbQosCMDropPkt64'] : null;
                } else {
                    d_echo('Cisco CBQoS ' . $thisQos->title . ' not processed because it it not marked as ingress or egress');
                }
            } elseif ($thisQos->type == 'cisco_cbqos_policymap') {
                // No polling for policymap
            } else {
                d_echo('Cisco CBQoS ' . $thisQos->type . ' not implemented in LibreNMS/OS/Shared/Cisco.php');
            }
        }
    }
}
