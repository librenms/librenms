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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 * @copyright  2018 Jose Augusto Cardoso
 */

namespace LibreNMS\OS\Shared;

use App\Models\Device;
use App\Models\PortsNac;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use LibreNMS\OS\Traits\YamlOSDiscovery;
use LibreNMS\Util\IP;

class Cisco extends OS implements OSDiscovery, ProcessorDiscovery, NacPolling
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

        if (empty($hardware) && ! empty($data[1000]['entPhysicalModelName'])) {
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

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors_data = snmpwalk_group($this->getDeviceArray(), 'cpmCPU', 'CISCO-PROCESS-MIB');
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

            $entPhysicalIndex = $entry['cpmCPUTotalPhysicalIndex'];

            if ($entPhysicalIndex) {
                if ($this->isCached('entPhysicalName')) {
                    $entPhysicalName_array = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
                    $descr = $entPhysicalName_array[$entPhysicalIndex];
                }

                if (empty($descr)) {
                    $descr = snmp_get($this->getDeviceArray(), 'entPhysicalName.' . $entPhysicalIndex, '-Oqv', 'ENTITY-MIB');
                }
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
                        $entPhysicalIndex
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
                    $entPhysicalIndex
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
                if ($this->isCached('entPhysicalName')) {
                    $entPhysicalName_array = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
                    $qfp_descr = $entPhysicalName_array[$entQfpPhysicalIndex];
                }
                if (empty($qfp_descr)) {
                    $qfp_descr = snmp_get($this->getDeviceArray(), 'entPhysicalName.' . $entQfpPhysicalIndex, '-Oqv', 'ENTITY-MIB');
                }
            }

            if (empty($qfp_descr)) {
                $qfp_desc = "QFP $entQfpPhysicalIndex";
            }

            $processors[] = Processor::discover(
                'qfp',
                $this->getDeviceId(),
                $qfp_usage_oid,
                $entQfpPhysicalIndex . '.3',
                $qfp_descr,
                1,
                $qfp_usage,
                null,
                $entQfpPhysicalIndex
            );
        }

        return $processors;
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
