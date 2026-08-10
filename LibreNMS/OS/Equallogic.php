<?php

namespace LibreNMS\OS;

use App\Models\Storage;
use Illuminate\Support\Collection;
use LibreNMS\OS;

class Equallogic extends OS
{
    public function discoverStorage(): Collection
    {
        return \SnmpQuery::mibDir('equallogic')->walk([
            'EQLVOLUME-MIB::eqliscsiVolumeTable',
            'EQLVOLUME-MIB::eqliscsiVolumeStatusTable',
        ])->mapTable(fn ($data, $eqliscsiLocalMemberId, $eqliscsiVolumeIndex) => (new Storage([
            'type' => 'equallogic',
            'storage_type' => $data['EQLVOLUME-MIB::eqliscsiVolumeAdminStatus'] ?? null,
            'storage_descr' => $data['EQLVOLUME-MIB::eqliscsiVolumeName'] ?? "volume $eqliscsiVolumeIndex",
            'storage_index' => "$eqliscsiLocalMemberId.$eqliscsiVolumeIndex",
            'storage_units' => 1000000,
            'storage_used_oid' => ".1.3.6.1.4.1.12740.5.1.7.7.1.13.$eqliscsiLocalMemberId.$eqliscsiVolumeIndex",
        ]))->fillUsage($data['EQLVOLUME-MIB::eqliscsiVolumeStatusAllocatedSpace'] ?? null, $data['EQLVOLUME-MIB::eqliscsiVolumeSize'] ?? null));
    }
}
