<?php

namespace LibreNMS\OS;

use App\Models\Storage;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\StorageDiscovery;
use LibreNMS\Interfaces\Polling\StoragePolling;
use LibreNMS\OS;
use SnmpQuery;

class Intelliflash extends OS implements StorageDiscovery, StoragePolling
{
    public function discoverStorage(): Collection
    {
        $pools = SnmpQuery::walk('TEGILE-MIB::poolTable')->mapTable(function ($data, $poolIndex) {
            $size = ($data['TEGILE-MIB::poolSizeHigh'] << 32) + $data['TEGILE-MIB::poolSizeLow'];
            $used = ($data['TEGILE-MIB::poolUsedSizeHigh'] << 32) + $data['TEGILE-MIB::poolUsedSizeLow'];

            return (new Storage([
                'type' => 'intelliflash-pl',
                'storage_index' => $poolIndex,
                'storage_type' => $data['TEGILE-MIB::poolState'],
                'storage_descr' => $data['TEGILE-MIB::poolName'],
                'storage_size' => $size,
                'storage_used' => $used,
                'storage_units' => 1,
            ]))->fillUsage($used, $size);
        });

        $projects = SnmpQuery::walk('TEGILE-MIB::projectTable')->mapTable(function ($data, $poolIndex, $projectIndex) {
            $used = ($data['TEGILE-MIB::projectDataSizeHigh'] << 32) + $data['TEGILE-MIB::projectDataSizeLow'];
            $free = ($data['TEGILE-MIB::projectFreeSizeHigh'] << 32) + $data['TEGILE-MIB::projectFreeSizeLow'];

            return (new Storage([
                'type' => 'intelliflash-pr',
                'storage_index' => "$poolIndex.$projectIndex",
                'storage_type' => $data['TEGILE-MIB::projectCompressionEnabled'],
                'storage_descr' => $data['TEGILE-MIB::projectName'],
                'storage_used' => $used,
                'storage_free' => $free,
                'storage_units' => 1,
            ]))->fillUsage($used, free: $free);
        });

        return $pools->merge($projects);
    }

    public function pollStorage(Collection $storages): Collection
    {
        $pools = SnmpQuery::walk([
            'TEGILE-MIB::poolSizeHigh',
            'TEGILE-MIB::poolSizeLow',
            'TEGILE-MIB::poolUsedSizeHigh',
            'TEGILE-MIB::poolUsedSizeLow',
        ])->valuesByIndex();
        $projects = SnmpQuery::walk([
            'TEGILE-MIB::projectDataSizeHigh',
            'TEGILE-MIB::projectDataSizeLow',
            'TEGILE-MIB::projectFreeSizeHigh',
            'TEGILE-MIB::projectFreeSizeLow',
        ])->valuesByIndex();

        return $storages->each(function (Storage $storage) use ($pools, $projects) {
            if ($storage->type == 'intelliflash-pl') {
                $size = ($pools[$storage->storage_index]['TEGILE-MIB::poolSizeHigh'] << 32) + $pools[$storage->storage_index]['TEGILE-MIB::poolSizeLow'];
                $used = ($pools[$storage->storage_index]['TEGILE-MIB::poolUsedSizeHigh'] << 32) + $pools[$storage->storage_index]['TEGILE-MIB::poolUsedSizeLow'];
                $storage->fillUsage($used, $size);
            } elseif ($storage->type == 'intelliflash-pr') {
                $used = ($projects[$storage->storage_index]['TEGILE-MIB::projectDataSizeHigh'] << 32) + $projects[$storage->storage_index]['TEGILE-MIB::projectDataSizeLow'];
                $free = ($projects[$storage->storage_index]['TEGILE-MIB::projectFreeSizeHigh'] << 32) + $projects[$storage->storage_index]['TEGILE-MIB::projectFreeSizeLow'];
                $storage->fillUsage($used, free: $free);
            }
        });
    }
}
