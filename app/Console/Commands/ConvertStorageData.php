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
                            'storage_descr' => $storage['storage_descr'],
                            'storage_size' => $storage['storage_size'],
                            'storage_size_oid' => null,
                            'storage_units' => $this->getStorageUnits($snmprec_file, $storage),
                            'storage_used' => $this->getStorageUsed($snmprec_file, $storage),
                            'storage_used_oid' => $this->getUsedOid($snmprec_file, $storage),
                            'storage_free' => $data['storage']['poller'][$table][$index]['storage_free'] ?: $storage['storage_free'],
                            'storage_free_oid' => $this->getFreeOid($snmprec_file, $storage),
                            'storage_perc' => $data['storage']['poller'][$table][$index]['storage_perc'] ?: $storage['storage_perc'],
                            'storage_perc_oid' => $this->getPercOid($snmprec_file, $storage),
                            'storage_perc_warn' => $storage['storage_perc_warn'],
                        ];
                    }

                    // if removed rows, reset indices
                    if ($removed_rows) {
                        if (empty($data['storage'][$type][$table])) {
                            unset($data['storage'][$type][$table]);
                        } else {
                            $data['storage'][$type][$table] = array_values($data['storage'][$type][$table]);
                        }
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

        return 'Storage';
    }

    private function getStorageUnits(string $snmprec_file, array $storage): ?int
    {
        if (Str::startsWith($snmprec_file, 'eltex-mes')) {
            return 1;
        }

        return $storage['storage_units'];
    }
    private function getStorageUsed(string $snmprec_file, array $storage): ?int
    {
        if ($snmprec_file == 'arbos_tms') {
            return 8;
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

        return null;
    }

    private function shouldSkip(string $snmprec_file, $storage): bool
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

        return false;
    }
}
