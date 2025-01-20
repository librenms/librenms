<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ConvertStorageData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:storage-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert storage test data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (glob(base_path('tests/data/*.json')) as $file) {
            $data = json_decode(file_get_contents($file), true);

            if (empty($data['storage'])) {
//                $this->line('skipped: ' . basename($file));
                continue;
            }

            $snmprec_file = basename($file, '.json');

            foreach ($data['storage'] as $type => $tables) {
                foreach ($tables as $table => $row) {
                    $removed_rows = false;

                    foreach ($row as $index => $storage) {
                        if ($this->shouldSkip($snmprec_file, $storage)) {
                            unset($data['storage'][$type][$table][$index]);
                            $removed_rows = true;
                            continue; // remove stupid entry
                        }

                        $data['storage'][$type][$table][$index] = [
                            'type' => $storage['storage_mib'],
                            'storage_index' => $storage['storage_index'],
                            'storage_type' => $this->getStorageType($snmprec_file, $storage),
                            'storage_descr' => str_replace('\\\\', '\\', $storage['storage_descr']),
                            'storage_size' => $this->getStorageSize($snmprec_file, $storage, $type),
                            'storage_size_oid' => null,
                            'storage_units' => $this->getStorageUnits($snmprec_file, $storage),
                            'storage_used' => $this->getStorageUsed($snmprec_file, $storage, $type),
                            'storage_used_oid' => $this->getUsedOid($snmprec_file, $storage),
                            'storage_free' => $this->getStorageFree($snmprec_file, $storage, $data['storage']['poller'][$table][$index]['storage_free'], $type),
                            'storage_free_oid' => $this->getFreeOid($snmprec_file, $storage),
                            'storage_perc' => $data['storage']['poller'][$table][$index]['storage_perc'] ?: $storage['storage_perc'],
                            'storage_perc_oid' => $this->getPercOid($snmprec_file, $storage),
                            'storage_perc_warn' => $storage['storage_perc_warn'],
                        ];
                    }

                    // fix sort orders to match current dump sorting
                    usort($data['storage'][$type][$table], function ($a, $b) {
                        // sort by type first
                        if ($a['type'] != $b['type']) {
                            return $a['type'] <=> $b['type'];
                        }

                        return strcmp($a['storage_index'], $b['storage_index']);
                    });

                    // if removed rows, reset indices
                    if ($removed_rows) {
                        if (empty($data['storage'][$type][$table])) {
                            unset($data['storage'][$type][$table]);
                        } else {
                            $data['storage'][$type][$table] = array_values($data['storage'][$type][$table]);
                        }
                    }

                    // set matches discovery if appropriate
                    if ($type == 'poller' && ! empty($data['storage']['discovery'][$table]) && $data['storage']['discovery'][$table] == $data['storage']['poller'][$table]) {
                        $data['storage']['poller'] = 'matches discovery';
                    }
                }
            }

            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
//            $this->info('updated: ' . basename($file));
        }
    }

    private function getStorageType(string $snmprec_file, array $storage): ?string
    {
        if ($storage['storage_mib'] == 'hrstorage') {
            if ($snmprec_file == 'allied_alliedware' && $storage['storage_index'] == '3') {
                return 'hrStorageRamDisk';
            }

            if (Str::startsWith($snmprec_file, 'arista_eos') && $storage['storage_index'] == '4') {
                return 'hrStorageFlashMemory';
            }

            if (Str::startsWith($snmprec_file, 'audiocodes') && $storage['storage_index'] == '2') {
                return 'hrStorageFlashMemory';
            }

            if (str::startsWith($snmprec_file, 'ciena')) {
                return 'hrDeviceTypes.9'; // derp ciena
            }

            if (str::startsWith($snmprec_file, 'ibos') && $storage['storage_index'] == '3') {
                return 'hrStorageFlashMemory';
            }

            return 'hrStorageFixedDisk';
        }

        if ($storage['storage_mib'] == 'ucd-dsktable') {
            return 'ucdDisk';
        }

        if ($storage['storage_mib'] == 'aix') {
            return $storage['storage_index'] == 12 ? 'jfs' : 'jfs2';
        }

        if ($storage['storage_mib'] == 'datadomain') {
            return 'Active Tier';
        }

        if ($storage['storage_mib'] == 'ericsson-ipos') {
            return 'flashMemory';
        }

        if ($storage['storage_mib'] == 'intelliflash-pl') {
            return 'ONLINE';
        }

        if ($storage['storage_mib'] == 'intelliflash-pr') {
            if($storage['storage_index'] == '3.1' || $storage['storage_index'] == '3.2') {
                return 'lz4';
            }

            if($storage['storage_index'] == '3.3') {
                return 'gzip-9';
            }
        }

        return 'Storage';
    }

    private function getStorageUnits(string $snmprec_file, array $storage): ?int
    {
        if (Str::startsWith($snmprec_file, 'eltex-mes')) {
            return 1;
        }

        if ($storage['storage_mib'] == 'forcepoint') {
            return 1024;
        }

        return $storage['storage_units'];
    }

    private function getStorageUsed(string $snmprec_file, array $storage, string $data_type): int|float|null
    {
        if ($snmprec_file == 'arbos_tms') {
            return 8;
        }

        if ($storage['storage_mib'] == 'ericsson-ipos') {
            if ($storage['storage_index'] == '84') {
                return 4619112243;
            }

            if ($storage['storage_index'] == '85') {
                return 5054368358;
            }

            return $storage['storage_used'] * 1024;
        }

        if ($storage['storage_mib'] == 'hpe-ilo' && $data_type == 'discovery') {
            return $storage['storage_used'] * 1048576;
        }

        if (is_numeric($storage['storage_used'])) {
            return (int) $storage['storage_used'];
        }

        return null;
    }

    private function getUsedOid(string $snmprec_file, array $storage): ?string
    {
        if ($storage['storage_mib'] == 'hrstorage') {
            return '.1.3.6.1.2.1.25.2.3.1.6.' . $storage['storage_index'];
        }

        if ($storage['storage_mib'] == 'ucd-dsktable') {
            return '.1.3.6.1.4.1.2021.9.1.8.' . $storage['storage_index'];
        }

        if ($storage['storage_mib'] == 'datadomain') {
            return '.1.3.6.1.4.1.19746.1.3.2.1.1.5.' . $storage['storage_index'];
        }

        if ($storage['storage_mib'] == 'forcepoint') {
            return '.1.3.6.1.4.1.1369.5.2.1.11.3.1.5.' . $storage['storage_index'];
        }

        if ($storage['storage_mib'] == 'hpe-ilo') {
            return '.1.3.6.1.4.1.232.11.2.4.1.1.4.' . $storage['storage_index'];
        }

        return null;
    }

    private function getFreeOid(string $snmprec_file, array $storage): ?string
    {
        if ($storage['storage_mib'] == 'aix') {
            return '.1.3.6.1.4.1.2.6.191.6.2.1.6.' . $storage['storage_index'];
        }

        if ($storage['storage_mib'] == 'eltex-mes24xx') {
            return null;
        }

        if (Str::startsWith($snmprec_file, 'eltex-mes')) {
            return '.1.3.6.1.4.1.89.96.5.0';
        }

        return null;
    }

    private function getPercOid(string $snmprec_file, array $storage): ?string
    {
        if ($storage['storage_mib'] == 'arbos') {
            return '.1.3.6.1.4.1.9694.1.5.2.6.0';
        }

        if ($storage['storage_mib'] == 'eltex-mes24xx') {
            return '.1.3.6.1.4.1.2076.81.1.75.0';
        }

        if ($storage['storage_mib'] == 'ericsson-ipos') {
            return '.1.3.6.1.4.1.193.218.2.24.1.2.1.1.6.' . $storage['storage_index'];
        }

        return null;
    }

    private function shouldSkip(string $snmprec_file, array $storage): bool
    {
        if ($snmprec_file == 'aix_net-snmp' && $storage['storage_index'] == '42') {
            return true; // /aha (negative values)
        }

        if ($storage['storage_descr'] == '/sys/fs/cgroup') {
            return true;
        }

        if (Str::startsWith($storage['storage_descr'], '/run/user/')) {
            return true;
        }

        if ($snmprec_file == 'canonprinter_tm') {
            return true;
        }

        if (Str::startsWith($snmprec_file, 'dell-os10') && $storage['storage_mib'] == 'ucd-dsktable') {
            return true;
        }

        if (Str::startsWith($snmprec_file, 'esphome')) {
            return true;
        }

        if (Str::startsWith($snmprec_file, 'hpe-ilo')) {
            if ($storage['storage_mib'] == 'hrstorage') {
                return true;
            }

            if (preg_match('#^[^/]+ on /#', $storage['storage_descr'])) {
                return true;
            }
        }


        return false;
    }

    private function getStorageSize(string $snmprec_file, array $storage, string $data_type): int|float|null
    {
        if ($storage['storage_mib'] == 'ericsson-ipos') {
            return $storage['storage_size'] * 1024;
        }

        if ($snmprec_file === 'hpe-ilo_5_with_bat_checks' && $storage['storage_index'] == '33') {
            return 360066232352770;
        }

        if ($storage['storage_mib'] == 'hpe-ilo' && $data_type == 'discovery') {
            return $storage['storage_size'] * 1048576;
        }

        return $storage['storage_size'];
    }

    private function getStorageFree(string $snmprec_file, array $storage, $poller_free, string $data_type)
    {
        $value = $poller_free ?: $storage['storage_free'];

        if ($storage['storage_mib'] == 'ericsson-ipos') {
            if ($storage['storage_index'] == '84') {
                return 30912520397;
            }

            if ($storage['storage_index'] == '85') {
                return 28641420698;
            }

            return $storage['storage_used'] * 1024;
        }

        if ($snmprec_file === 'hpe-ilo_5_with_bat_checks' && $storage['storage_index'] == '33' && $data_type == 'poller') {
            return 78926617509890;
        }

        return $value;
    }
}
