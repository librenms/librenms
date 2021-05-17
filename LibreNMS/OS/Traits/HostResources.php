<?php
/**
 * HostResources.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Mempool;
use App\Models\Sla;
use Closure;
use Exception;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use Rrd;

trait HostResources
{
    private $hrStorage;
    private $memoryStorageTypes = [
        'hrStorageVirtualMemory',
        'hrStorageRam',
        'hrStorageOther',
    ];
    private $ignoreMemoryDescr = [
        'MALLOC',
        'UMA',
        'procfs',
        '/proc',
    ];
    private $validOtherMemory = [
        'Memory buffers',
        'Cached memory',
        'Shared memory',
    ];
    private $memoryDescrWarn = [
        'Cached memory' => 0,
        'Memory buffers' => 0,
        'Physical memory' => 99,
        'Real memory' => 90,
        'Shared memory' => 0,
        'Swap space' => 10,
        'Virtual memory' => 95,
    ];

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        echo 'Host Resources: ';
        $processors = [];

        try {
            $hrProcessorLoad = $this->getCacheByIndex('hrProcessorLoad', 'HOST-RESOURCES-MIB');

            if (empty($hrProcessorLoad)) {
                // no hr data, return
                return [];
            }

            $hrDeviceDescr = $this->getCacheByIndex('hrDeviceDescr', 'HOST-RESOURCES-MIB');
        } catch (Exception $e) {
            return [];
        }

        foreach ($hrProcessorLoad as $index => $usage) {
            $usage_oid = '.1.3.6.1.2.1.25.3.3.1.2.' . $index;
            $descr = $hrDeviceDescr[$index];

            if (! is_numeric($usage)) {
                continue;
            }

            $device = $this->getDeviceArray();
            if ($device['os'] == 'arista-eos' && $index == '1') {
                continue;
            }

            if (empty($descr)
                || $descr == 'Unknown Processor Type' // Windows: Unknown Processor Type
                || $descr == 'An electronic chip that makes the computer work.'
            ) {
                $descr = 'Processor';
            } else {
                // Make the description a bit shorter
                $remove_strings = [
                    'GenuineIntel: ',
                    'AuthenticAMD: ',
                    'CPU ',
                    '(TM)',
                    '(R)',
                ];
                $descr = str_replace($remove_strings, '', $descr);
                $descr = str_replace('  ', ' ', $descr);
            }

            $old_name = ['hrProcessor', $index];
            $new_name = ['processor', 'hr', $index];
            Rrd::renameFile($this->getDeviceArray(), $old_name, $new_name);

            $processor = Processor::discover(
                'hr',
                $this->getDeviceId(),
                $usage_oid,
                $index,
                $descr,
                1,
                $usage,
                null,
                null,
                $index
            );

            if ($processor->isValid()) {
                $processors[] = $processor;
            }
        }

        return $processors;
    }

    public function discoverMempools()
    {
        $hr_storage = $this->getCacheTable('hrStorageTable', 'HOST-RESOURCES-MIB:HOST-RESOURCES-TYPES');

        if (! is_array($hr_storage)) {
            return collect();
        }

        $ram_bytes = snmp_get($this->getDeviceArray(), 'hrMemorySize.0', '-OQUv', 'HOST-RESOURCES-MIB') * 1024
            ?: (isset($hr_storage[1]['hrStorageSize']) ? $hr_storage[1]['hrStorageSize'] * $hr_storage[1]['hrStorageAllocationUnits'] : 0);

        return collect($hr_storage)->filter(Closure::fromCallable([$this, 'memValid']))
            ->map(function ($storage, $index) use ($ram_bytes) {
                $total = $storage['hrStorageSize'];
                if (Str::contains($storage['hrStorageDescr'], 'Real Memory Metrics') || ($storage['hrStorageType'] == 'hrStorageOther' && $total != 0)) {
                    // use total RAM for buffers, cached, and shared
                    // bsnmp does not report the same as net-snmp, total RAM is stored in hrMemorySize
                    if ($ram_bytes) {
                        $total = $ram_bytes / $storage['hrStorageAllocationUnits']; // will be calculated with this entries allocation units later
                    }
                }

                return (new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'hrstorage',
                    'mempool_precision' => $storage['hrStorageAllocationUnits'],
                    'mempool_descr' => $storage['hrStorageDescr'],
                    'mempool_perc_warn' => $this->memoryDescrWarn[$storage['hrStorageDescr']] ?? 90,
                    'mempool_used_oid' => ".1.3.6.1.2.1.25.2.3.1.6.$index",
                    'mempool_total_oid' => null,
                ]))->setClass(null, $storage['hrStorageType'] == 'hrStorageVirtualMemory' ? 'virtual' : 'system')
                    ->fillUsage($storage['hrStorageUsed'], $total);
            });
    }

    public function discoverSlas()
    {
        $device = $this->getDeviceArray();

        $slas = collect();
        $data = snmp_walk($device, 'pingMIB.pingObjects.pingCtlTable.pingCtlEntry', '-OQUs', '+DISMAN-PING-MIB');

        // Index the MIB information
        $sla_table = [];
        foreach (explode("\n", $data) as $index) {
            $key_val = explode(' ', $index, 3);

            $key = $key_val[0];
            $value = $key_val[2];

            $prop_id = explode('.', $key);

            $property = $prop_id[0];
            $owner = $prop_id[1];
            $test = $prop_id[2];

            $sla_table[$owner . '.' . $test][$property] = $value;
        }

        foreach ($sla_table as $sla_key => $sla_config) {
            // To get right owner index and test name from $sla_table key
            $prop_id = explode('.', $sla_key);
            $owner = $prop_id[0];
            $test = $prop_id[1];

            $sla_data = Sla::select('sla_id', 'sla_nr')
                ->where('device_id', $device['device_id'])
                ->where('owner', $owner)
                ->where('tag', $test)
                ->get();

            $sla_id = $sla_data[0]->sla_id;
            $sla_nr = $sla_data[0]->sla_nr;

            $data = [
                'device_id' => $device['device_id'],
                'sla_nr'    => $sla_nr,
                'owner'     => $owner,
                'tag'       => $test,
                'rtt_type'  => $sla_config['pingCtlType'],
                'status'    => ($sla_config['pingCtlAdminStatus'] == 'enabled') ? 1 : 0,
                'opstatus'  => ($sla_config['pingCtlRowStatus'] == 'active') ? 0 : 2,
                'deleted'   => 0,
            ];

            // If it is a standard type delete ping preffix
            $data['rtt_type'] = str_replace('ping', '', $data['rtt_type']);

            // To retrieve specific Juniper PingCtlType
            if ($device['os'] == "junos") {
                $data['rtt_type'] = $this->retrieveJuniperType($data['rtt_type']);
            }

            $slas->push($data);
        }
        return $slas;
    }

    protected function memValid($storage)
    {
        if (! in_array($storage['hrStorageType'], $this->memoryStorageTypes)) {
            return false;
        }

        if ($storage['hrStorageType'] == 'hrStorageOther' && ! in_array($storage['hrStorageDescr'], $this->validOtherMemory)) {
            return false;
        }

        return ! Str::contains($storage['hrStorageDescr'], $this->ignoreMemoryDescr);
    }
}
