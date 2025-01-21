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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Mempool;
use App\Models\Storage;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Util\Number;
use Rrd;
use SnmpQuery;

trait HostResources
{
    private $hrStorage;
    private $memoryStorageTypes = [
        'hrStorageVirtualMemory',
        'hrStorageRam',
        'hrStorageOther',
    ];
    private $storageIgnoreTypes = [
        'hrStorageVirtualMemory',
        'hrStorageRam',
        'hrStorageOther',
        'nwhrStorageDOSMemory',
        'nwhrStorageMemoryAlloc',
        'nwhrStorageMemoryPermanent',
        'nwhrStorageCacheBuffers',
        'nwhrStorageCacheMovable',
        'nwhrStorageCacheNonMovable',
        'nwhrStorageCodeAndDataMemory',
        'nwhrStorageIOEngineMemory',
        'nwhrStorageMSEngineMemory',
        'nwhrStorageUnclaimedMemory',
    ];
    private $hrTypes = [
        1 => 'hrStorageOther',
        2 => 'hrStorageRam',
        3 => 'hrStorageVirtualMemory',
        4 => 'hrStorageFixedDisk',
        5 => 'hrStorageRemovableDisk',
        6 => 'hrStorageFloppyDisk',
        7 => 'hrStorageCompactDisc',
        8 => 'hrStorageRamDisk',
        9 => 'hrStorageFlashMemory',
        10 => 'hrStorageNetworkDisk',
    ];
    private $ignoreMemoryDescr = [
        'malloc',
        'uma',
        'procfs',
        '/proc',
    ];
    private $validOtherMemory = [
        'memory buffers',
        'cached memory',
        'memory cache',
        'shared memory',
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
        Log::info('Host Resources: ');
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
            $descr = $hrDeviceDescr[$index] ?? null;

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

    public function discoverMempools(): Collection
    {
        $hr_storage = SnmpQuery::cache()->hideMib()->mibs(['HOST-RESOURCES-TYPES'])->walk('HOST-RESOURCES-MIB::hrStorageTable')->table(1);
        $this->fixBadData($hr_storage);

        if (empty($hr_storage)) {
            return new Collection;
        }

        $hrMemorySize = (int) SnmpQuery::get('HOST-RESOURCES-MIB::hrMemorySize.0')->value();
        $ram_bytes = $hrMemorySize
            ? $hrMemorySize * 1024
            : (isset($hr_storage[1]['hrStorageSize']) ? $hr_storage[1]['hrStorageSize'] * $hr_storage[1]['hrStorageAllocationUnits'] : 0);

        return collect($hr_storage)->filter($this->memValid(...))
            ->map(function ($storage, $index) use ($ram_bytes) {
                $total = $storage['hrStorageSize'] ?? null;
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
                    ->fillUsage($storage['hrStorageUsed'] ?? null, $total);
            });
    }

    public function discoverStorage(): Collection
    {
        $hr_storage = SnmpQuery::cache()->hideMib()->mibs(['HOST-RESOURCES-TYPES'])->walk('HOST-RESOURCES-MIB::hrStorageTable')->table(1);
        $this->fixBadData($hr_storage);

        if (empty($hr_storage)) {
            return new Collection;
        }

        return collect($hr_storage)->filter(function ($storage, $index) {
            if (empty($storage['hrStorageType'])) {
                Log::debug("Host Resources: skipped storage ($index) due to missing hrStorageType");

                return false;
            }

            if (! isset($storage['hrStorageUsed']) || $storage['hrStorageUsed'] < 0) {
                Log::debug("Host Resources: skipped storage ($index) due to missing or negative hrStorageUsed");

                return false;
            }

            if (! isset($storage['hrStorageSize']) || $storage['hrStorageSize'] <= 0) {
                Log::debug("Host Resources: skipped storage ($index) due to missing, negative, or 0 hrStorageSize");

                return false;
            }

            return ! in_array($storage['hrStorageType'], $this->storageIgnoreTypes);
        })->map(function ($storage) {
            return (new Storage([
                'type' => 'hrstorage',
                'storage_index' => $storage['hrStorageIndex'],
                'storage_type' => $storage['hrStorageType'],
                'storage_descr' => $storage['hrStorageDescr'],
                'storage_used_oid' => '.1.3.6.1.2.1.25.2.3.1.6.' . $storage['hrStorageIndex'],
                'storage_units' => $storage['hrStorageAllocationUnits'],
            ]))->fillUsage(
                Number::correctIntegerOverflow($storage['hrStorageUsed'] ?? null),
                Number::correctIntegerOverflow($storage['hrStorageSize'] ?? null),
            );
        });
    }

    protected function memValid($storage): bool
    {
        if (empty($storage['hrStorageType']) || empty($storage['hrStorageDescr'])) {
            Log::debug("hrStorageIndex {$storage['hrStorageIndex']} invalid: empty hrStorageType or hrStorageDescr");

            return false;
        }

        if (! in_array($storage['hrStorageType'], $this->memoryStorageTypes)) {
            Log::debug("hrStorageIndex {$storage['hrStorageIndex']} invalid: bad hrStorageType ({$storage['hrStorageType']})");

            return false;
        }

        $hrStorageDescr = strtolower($storage['hrStorageDescr']);

        if ($storage['hrStorageType'] == 'hrStorageOther' && ! in_array($hrStorageDescr, $this->validOtherMemory)) {
            Log::debug("hrStorageIndex {$storage['hrStorageIndex']} invalid: hrStorageOther & not an exception");

            return false;
        }

        if (Str::contains($hrStorageDescr, $this->ignoreMemoryDescr)) {
            Log::debug("hrStorageIndex {$storage['hrStorageIndex']} invalid: bad hrStorageDescr ({$hrStorageDescr})");

            return false;
        }

        return true;
    }

    protected function fixBadData(array &$data): void
    {
        foreach ($data as $index => $entry) {
            if (isset($entry['hrStorageType'])) {
                $data[$index]['hrStorageType'] = $this->fixBadTypes($entry['hrStorageType']);
            }
        }
    }

    protected function fixBadTypes($hrStorageType): string
    {
        if (str_starts_with($hrStorageType, 'hrStorage')) {
            // fix some that set them incorrectly as scalars
            return preg_replace('/\.0$/', '', $hrStorageType);
        }

        // if the agent returns with a bad base oid, just take the last index off the oid and manually convert it
        if (preg_match('/\.(\d+)$/', $hrStorageType, $matches)) {
            if (isset($this->hrTypes[$matches[1]])) {
                return $this->hrTypes[$matches[1]];
            }
        }

        if (str_starts_with($hrStorageType, 'hr')) {
            return $hrStorageType; // pass through other types (such as fs)
        }

        Log::debug("Could not fix bad hrStorageType: $hrStorageType");

        return 'unknown';
    }
}
