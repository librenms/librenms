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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 * @copyright  2018 Jose Augusto Cardoso
 */

namespace LibreNMS\OS\Shared;

use App\Models\PortsNac;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use LibreNMS\Util\IP;

class Cisco extends OS implements ProcessorDiscovery, NacPolling
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors_data = snmpwalk_group($this->getDevice(), 'cpmCPU', 'CISCO-PROCESS-MIB');
        $processors = array();

        foreach ($processors_data as $index => $entry) {
            if (is_numeric($entry['cpmCPUTotal5minRev'])) {
                $usage_oid = '.1.3.6.1.4.1.9.9.109.1.1.1.1.8.'.$index;
                $usage     = $entry['cpmCPUTotal5minRev'];
            } elseif (is_numeric($entry['cpmCPUTotal5min'])) {
                $usage_oid = '.1.3.6.1.4.1.9.9.109.1.1.1.1.5.'.$index;
                $usage     = $entry['cpmCPUTotal5min'];
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
                    $descr = snmp_get($this->getDevice(), 'entPhysicalName.'.$entPhysicalIndex, '-Oqv', 'ENTITY-MIB');
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
        $qfp_data = snmpwalk_group($this->getDevice(), 'ceqfpUtilProcessingLoad', 'CISCO-ENTITY-QFP-MIB');

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
                    $qfp_descr = snmp_get($this->getDevice(), 'entPhysicalName.'.$entQfpPhysicalIndex, '-Oqv', 'ENTITY-MIB');
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

        $portAuthSessionEntry = snmpwalk_cache_oid($this->getDevice(), 'cafSessionEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB');
        if (!empty($portAuthSessionEntry)) {
            $cafSessionMethodsInfoEntry = collect(snmpwalk_cache_oid($this->getDevice(), 'cafSessionMethodsInfoEntry', [], 'CISCO-AUTH-FRAMEWORK-MIB'))->mapWithKeys(function ($item, $key) {
                $key_parts = explode('.', $key);
                $key = implode('.', array_slice($key_parts, 0, 2)); // remove the auth method
                return [$key => ['method' => $key_parts[2], 'authc_status' => $item['cafSessionMethodState']]];
            });

            // cache port ifIndex -> port_id map
            $ifIndex_map = $this->getDeviceModel()->ports()->pluck('port_id', 'ifIndex');

            // update the DB
            foreach ($portAuthSessionEntry as $index => $portAuthSessionEntryParameters) {
                list($ifIndex, $auth_id) = explode('.', str_replace("'", '', $index));
                $session_info = $cafSessionMethodsInfoEntry->get($ifIndex . '.' . $auth_id);
                $mac_address = strtolower(implode(array_map('zeropad', explode(':', $portAuthSessionEntryParameters['cafSessionClientMacAddress']))));

                $nac->put($mac_address, new PortsNac([
                    'port_id' => $ifIndex_map->get($ifIndex, 0),
                    'mac_address' => $mac_address,
                    'auth_id' => $auth_id,
                    'domain' => $portAuthSessionEntryParameters['cafSessionDomain'],
                    'username' => $portAuthSessionEntryParameters['cafSessionAuthUserName'],
                    'ip_address' => (string)IP::fromHexString($portAuthSessionEntryParameters['cafSessionClientAddress'], true),
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
}
