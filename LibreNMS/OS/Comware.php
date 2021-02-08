<?php
/**
 * Comware.php
 *
 * H3C/HPE Comware OS
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
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;

class Comware extends OS implements MempoolsDiscovery, ProcessorDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        // serial
        $serial_nums = explode("\n", snmp_walk($this->getDeviceArray(), 'hh3cEntityExtManuSerialNum', '-Osqv', 'HH3C-ENTITY-EXT-MIB'));
        $this->getDevice()->serial = $serial_nums[0]; // use the first s/n
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $processors = [];
        $procdata = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtCpuUsage', 'HH3C-ENTITY-EXT-MIB');

        if (empty($procdata)) {
            return $processors;
        }
        $entity_data = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');

        foreach ($procdata as $index => $usage) {
            if ($usage['hh3cEntityExtCpuUsage'] != 0) {
                $processors[] = Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    ".1.3.6.1.4.1.25506.2.6.1.1.1.1.6.$index",
                    $index,
                    $entity_data[$index],
                    1,
                    $usage['hh3cEntityExtCpuUsage'],
                    null,
                    $index
                );
            }
        }

        return $processors;
    }

    public function discoverMempools()
    {
        $mempools = collect();
        $data = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtMemUsage', 'HH3C-ENTITY-EXT-MIB');

        if (empty($data)) {
            return $mempools; // avoid additional walks
        }

        $data = snmpwalk_group($this->getDeviceArray(), 'hh3cEntityExtMemSize', 'HH3C-ENTITY-EXT-MIB', 1, $data);
        $entity_name = $this->getCacheByIndex('entPhysicalName', 'ENTITY-MIB');
        $entity_class = $this->getCacheByIndex('entPhysicalClass', 'ENTITY-MIB');

        foreach ($data as $index => $entry) {
            if ($entity_class[$index] == 'module' && $entry['hh3cEntityExtMemUsage'] > 0) {
                $mempools->push((new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'comware',
                    'mempool_class' =>'system',
                    'mempool_descr' => $entity_name[$index],
                    'mempool_precision' => 1,
                    'mempool_perc_oid' => ".1.3.6.1.4.1.25506.2.6.1.1.1.1.8.$index",
                ]))->fillUsage(null, $entry['hh3cEntityExtMemSize'], null, $entry['hh3cEntityExtMemUsage']));
            }
        }

        return $mempools;
    }
}
