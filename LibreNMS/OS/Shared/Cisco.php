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
 */

namespace LibreNMS\OS\Shared;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Cisco extends OS implements ProcessorDiscovery
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

        return $processors;
    }
}
