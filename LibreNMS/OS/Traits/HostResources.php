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
use App\Models\Package;
use App\Models\Processor;
use App\Models\Storage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Exceptions\UncorrectableNegativeException;
use LibreNMS\Util\Number;
use LibreNMS\Util\StringHelpers;
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
     * Returns a Collection of Processor models that have been discovered
     *
     * @return Collection<Processor>
     */
    public function discoverProcessors(): Collection
    {
        Log::info('Host Resources: ');

        return SnmpQuery::abortOnFailure()->walk([
            'HOST-RESOURCES-MIB::hrProcessorLoad',
            'HOST-RESOURCES-MIB::hrDeviceDescr',
        ])->mapTable(function ($data, $hrDeviceIndex) {
            if (! isset($data['HOST-RESOURCES-MIB::hrProcessorLoad']) || ! is_numeric($data['HOST-RESOURCES-MIB::hrProcessorLoad'])) {
                return null;
            }

            // FIXME ? use skip_values
            if ($this->getName() == 'arista-eos' && $hrDeviceIndex == '1') {
                return null;
            }

            $descr = $data['HOST-RESOURCES-MIB::hrDeviceDescr'] ?? null;
            if (empty($descr)
                || $descr == 'Unknown Processor Type' // Windows: Unknown Processor Type
                || $descr == 'An electronic chip that makes the computer work.'
            ) {
                $descr = 'Processor';
            } else {
                // Make the description a bit shorter
                $descr = str_replace(['GenuineIntel: ', 'AuthenticAMD: ', 'CPU ', '(TM)', '(R)'], '', $descr);
                $descr = str_replace('  ', ' ', $descr);
            }

            return new Processor([
                'hrDeviceIndex' => $hrDeviceIndex,
                'processor_oid' => '.1.3.6.1.2.1.25.3.3.1.2.' . $hrDeviceIndex,
                'processor_index' => $hrDeviceIndex,
                'processor_type' => 'hr',
                'processor_precision' => 1,
                'processor_usage' => $data['HOST-RESOURCES-MIB::hrProcessorLoad'],
                'processor_descr' => $descr,
            ]);
        })->filter();
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

                return new Mempool([
                    'mempool_index' => $index,
                    'mempool_type' => 'hrstorage',
                    'mempool_precision' => $storage['hrStorageAllocationUnits'],
                    'mempool_descr' => $storage['hrStorageDescr'],
                    'mempool_perc_warn' => $this->memoryDescrWarn[$storage['hrStorageDescr']] ?? 90,
                    'mempool_used_oid' => ".1.3.6.1.2.1.25.2.3.1.6.$index",
                    'mempool_total_oid' => null,
                ])->setClass(null, $storage['hrStorageType'] == 'hrStorageVirtualMemory' ? 'virtual' : 'system')
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

        return collect($hr_storage)->map(function ($storage, $index) {
            // correct signed Integer32 wrap (e.g. Windows volumes > 2^31 allocation units) before validating
            // -1 is commonly used as "unknown", leave it negative so the entry is skipped
            if (isset($storage['hrStorageSize']) && is_numeric($storage['hrStorageSize']) && $storage['hrStorageSize'] < -1) {
                $storage['hrStorageSize'] = Number::correctIntegerOverflow($storage['hrStorageSize']);
            }

            if (isset($storage['hrStorageUsed'], $storage['hrStorageSize']) && is_numeric($storage['hrStorageUsed'])
                && $storage['hrStorageUsed'] < -1 && $storage['hrStorageSize'] > 0) {
                try {
                    $storage['hrStorageUsed'] = Number::correctIntegerOverflow($storage['hrStorageUsed'], (int) $storage['hrStorageSize']);
                } catch (UncorrectableNegativeException) {
                    Log::debug("Host Resources: could not correct negative hrStorageUsed for storage ($index), larger than hrStorageSize");
                }
            }

            return $storage;
        })->filter(function ($storage, $index) {
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
        })->map(fn ($storage) => new Storage([
            'type' => 'hrstorage',
            'storage_index' => $storage['hrStorageIndex'],
            'storage_type' => $storage['hrStorageType'],
            'storage_descr' => $storage['hrStorageDescr'],
            'storage_used_oid' => '.1.3.6.1.2.1.25.2.3.1.6.' . $storage['hrStorageIndex'],
            'storage_units' => $storage['hrStorageAllocationUnits'],
        ])->fillUsage(
            $storage['hrStorageUsed'] ?? null,
            $storage['hrStorageSize'] ?? null,
        ));
    }

    protected function memValid($storage): bool
    {
        if (! isset($storage['hrStorageIndex'])) {
            Log::debug('hrStorage invalid: missing hrStorageIndex');

            return false;
        }

        if (empty($storage['hrStorageType']) || empty($storage['hrStorageDescr'])) {
            Log::debug("hrStorageIndex {$storage['hrStorageIndex']} invalid: empty hrStorageType or hrStorageDescr");

            return false;
        }

        if (! in_array($storage['hrStorageType'], $this->memoryStorageTypes)) {
            Log::debug("hrStorageIndex {$storage['hrStorageIndex']} invalid: bad hrStorageType ({$storage['hrStorageType']})");

            return false;
        }

        $hrStorageDescr = strtolower((string) $storage['hrStorageDescr']);

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
        if (str_starts_with((string) $hrStorageType, 'hrStorage')) {
            // fix some that set them incorrectly as scalars
            return preg_replace('/\.0$/', '', (string) $hrStorageType);
        }

        // if the agent returns with a bad base oid, just take the last index off the oid and manually convert it
        if (preg_match('/\.(\d+)$/', (string) $hrStorageType, $matches)) {
            if (isset($this->hrTypes[$matches[1]])) {
                return $this->hrTypes[$matches[1]];
            }
        }

        if (str_starts_with((string) $hrStorageType, 'hr')) {
            return $hrStorageType; // pass through other types (such as fs)
        }

        Log::debug("Could not fix bad hrStorageType: $hrStorageType");

        return 'unknown';
    }

    /**
     * The installed software packages from the HOST-RESOURCES-MIB hrSWInstalledTable (RFC 1514).
     *
     * The hostres extension of the device's snmpd reports each installed package in
     * hrSWInstalledName as "name_version_arch" (dpkg layout). The table carries no
     * version column, the last modification date is available as hrSWInstalledDate.
     *
     * @return Collection<Package>
     */
    public function discoverPackages(): Collection
    {
        $response = SnmpQuery::walk('HOST-RESOURCES-MIB::hrSWInstalledTable');

        if (! $response->isValid()) {
            return new Collection;
        }

        return $response->mapTable(fn (array $row): ?Package => $this->makeHostResourcePackage($row))->filter()->values();
    }

    /**
     * @param  array<string, string>  $row
     */
    private function makeHostResourcePackage(array $row): ?Package
    {
        $raw = trim((string) ($row['HOST-RESOURCES-MIB::hrSWInstalledName'] ?? ''));

        if ($raw === '') {
            return null;
        }

        // snmpwalk renders names carrying non-printable characters (e.g. NUL padding) as a
        // whitespace-separated hex byte list, wrapped across lines. Require a multi-byte list
        // so a lone two-digit token like "bc" stays a plain name instead of one hex byte.
        $normalized = preg_replace('/\s+/', ' ', $raw);

        if (StringHelpers::isHex($normalized, ' ') && str_contains($normalized, ' ')) {
            $raw = StringHelpers::decodeSnmpHexText($raw);

            if ($raw === '') {
                return null;
            }
        }

        // the table has no version column, names without a version fall back to the last modification date
        ['name' => $name, 'version' => $version, 'build' => $build, 'arch' => $arch] = $this->parseInstalledName($raw, $this->parseHostResourceDate((string) ($row['HOST-RESOURCES-MIB::hrSWInstalledDate'] ?? '')));

        if ($version === '') {
            return null;
        }

        return new Package([
            'manager' => 'host-res',
            'name' => mb_substr($name, 0, 128),
            'arch' => mb_substr($arch, 0, 16),
            'version' => mb_substr($version, 0, 255),
            'build' => mb_substr($build, 0, 64),
            'size' => null,
        ]);
    }

    /**
     * Split an installed software name into its parts.
     * dpkg reports "name_version_arch", rpm reports "[epoch:]name-version-release"
     * without an architecture.
     *
     * @return array{name: string, version: string, build: string, arch: string}
     */
    private function parseInstalledName(string $raw, string $date): array
    {
        if (($parsed = $this->splitDpkgName($raw)) !== null) {
            return $parsed;
        }

        if (($parsed = $this->splitRpmName($raw)) !== null) {
            return $parsed;
        }

        return ['name' => $raw, 'version' => $date, 'build' => '', 'arch' => 'unknown'];
    }

    /**
     * dpkg architectures terminating a "name_version_arch" name
     *
     * @var string[]
     */
    private const array DPKG_ARCHS = [
        'all',
        'amd64',
        'i386',
        'i686',
        'arm64',
        'aarch64',
        'armhf',
        'armel',
        's390x',
        'ppc64el',
        'ppc64',
        'riscv64',
        'mips64el',
        'mipsel',
        'loong64',
        'x32',
        'm68k',
        'sh4',
        'sparc64',
        'ia64',
        'hurd-i386',
        'kfreebsd-amd64',
    ];

    /**
     * Split a dpkg "name_version_arch" name into its parts.
     * Neither dpkg names nor versions carry underscores, so the first underscore
     * ends the name and the last one ends the version.
     *
     * @return array{name: string, version: string, build: string, arch: string}|null
     */
    private function splitDpkgName(string $raw): ?array
    {
        $name_underscore = strpos($raw, '_');
        $arch_underscore = strrpos($raw, '_');

        if ($name_underscore === false || $arch_underscore === $name_underscore) {
            return null;
        }

        $arch = substr($raw, $arch_underscore + 1);
        if (! in_array($arch, self::DPKG_ARCHS, true)) {
            return null;
        }

        return [
            'name' => substr($raw, 0, $name_underscore),
            'version' => substr($raw, $name_underscore + 1, $arch_underscore - $name_underscore - 1),
            'build' => '',
            'arch' => $arch,
        ];
    }

    /**
     * Split an rpm "[epoch:]name-version-release" name into its parts.
     * rpm versions and releases carry no dashes, so the first dash in front of a
     * digit whose remainder holds at most one dash separates the name from the
     * version (the second dash separates the version from the release).
     *
     * @return array{name: string, version: string, build: string, arch: string}|null
     */
    private function splitRpmName(string $raw): ?array
    {
        $epoch = '';

        if (preg_match('/^(\d+):(.*)$/s', $raw, $matches) === 1) {
            $epoch = $matches[1] . ':';
            $raw = $matches[2];
        }

        $boundary = null;

        if (preg_match_all('/-(?=\d)/', $raw, $matches, PREG_OFFSET_CAPTURE) > 0) {
            foreach ($matches[0] as $match) {
                if (substr_count(substr($raw, $match[1] + 1), '-') <= 1) {
                    $boundary = $match[1];
                    break;
                }
            }
        }

        if ($boundary === null) {
            return null;
        }

        $name = substr($raw, 0, $boundary);

        if ($name === '') {
            return null;
        }

        $version = substr($raw, $boundary + 1);
        $build = '';

        $release_dash = strpos($version, '-');
        if ($release_dash !== false) {
            $build = substr($version, $release_dash + 1);
            $version = substr($version, 0, $release_dash);
        }

        return [
            'name' => $name,
            'version' => $epoch . $version,
            'build' => $build,
            'arch' => 'unknown',
        ];
    }

    /**
     * Parse a DateAndTime value into "Y-m-d H:i:s".
     * Handles the hostres rendering "2025-6-18,7:36:25.0,+2:0" and the standard
     * net-snmp rendering "+02:00 2025-06-18:07:36:25.0".
     */
    private function parseHostResourceDate(string $value): string
    {
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2}),(\d{1,2}):(\d{1,2}):(\d{1,2})\.\d+([,][+-]\d{1,2}:\d{1,2})?$/', $value, $matches)) {
            return $this->formatHostResourceDate($matches);
        }

        if (preg_match('/^[+-]\d{1,2}:\d{2}\s+(\d{4})-(\d{1,2})-(\d{1,2}):(\d{1,2}):(\d{1,2}):(\d{1,2})\.\d+$/', $value, $matches)) {
            return $this->formatHostResourceDate($matches);
        }

        return '';
    }

    /**
     * @param  array<int, string>  $matches
     */
    private function formatHostResourceDate(array $matches): string
    {
        return sprintf('%04d-%02d-%02d %02d:%02d:%02d', (int) $matches[1], (int) $matches[2], (int) $matches[3], (int) $matches[4], (int) $matches[5], (int) $matches[6]);
    }
}
