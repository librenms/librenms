<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class Storage extends DeviceRelatedModel implements Keyable
{
    protected $table = 'storage';
    protected $primaryKey = 'storage_id';

    public function getCompositeKey()
    {
        return "$this->storage_type-$this->storage_index";
    }
}
