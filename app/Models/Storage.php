<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class Storage extends DeviceRelatedModel implements Keyable
{
    protected $table = 'storage';
    protected $primaryKey = 'storage_id';
    protected $fillable = [
        'storage_mib',
        'storage_index',
        'storage_type',
        'storage_descr',
        'storage_size',
        'storage_size_oid',
        'storage_units',
        'storage_used',
        'storage_used_oid',
        'storage_free',
        'storage_free_oid',
        'storage_perc',
        'storage_perc_oid',
        'storage_perc_warn',
        'storage_deleted',
    ];

    public function getCompositeKey()
    {
        return "$this->storage_mib-$this->storage_index";
    }
}
