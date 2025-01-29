<?php

namespace LibreNMS\OS;

use App\Models\Storage;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Polling\StoragePolling;
use LibreNMS\OS;
use SnmpQuery;

class Nimbleos extends OS implements StoragePolling
{
    public function discoverStorage(): Collection
    {
        return SnmpQuery::walk('NIMBLE-MIB::volTable')
            ->mapTable(function ($data, $volIndex) {
                //nimble uses a high 32bit counter and a low 32bit counter to make a 64bit counter
                $used = ($data['NIMBLE-MIB::volUsageHigh'] << 32) + $data['NIMBLE-MIB::volUsageLow'];
                $size = ($data['NIMBLE-MIB::volSizeHigh'] << 32) + $data['NIMBLE-MIB::volSizeLow'];

                return (new Storage([
                    'type' => 'nimbleos',
                    'storage_index' => $volIndex,
                    'storage_type' => $data['NIMBLE-MIB::volOnline'],
                    'storage_descr' => $data['NIMBLE-MIB::volName'],
                    'storage_units' => 1048576,
                ]))->fillUsage($used, $size);
            });
    }

    public function pollStorage(Collection $storages): Collection
    {
        $data = SnmpQuery::walk(['NIMBLE-MIB::volSizeHigh', 'NIMBLE-MIB::volSizeLow'])->table(1);

        /** @var Storage $storage */
        foreach ($storages as $storage) {
            if (isset($data[$storage->storage_index])) {
                $used = ($data[$storage->storage_index]['NIMBLE-MIB::volUsageHigh'] << 32) + $data[$storage->storage_index]['NIMBLE-MIB::volUsageLow'];
                $storage->fillUsage($used, $storage->storage_size);
            }
        }

        return $storages;
    }
}
