<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class DiskIo extends DeviceRelatedModel implements Keyable
{
    public $timestamps = false;
    protected $table = 'ucd_diskio';
    protected $primaryKey = 'diskio_id';
    protected $fillable = [
        'diskio_id',
        'device_id',
        'diskio_index',
        'diskio_descr',
    ];

    public function getCompositeKey()
    {
        return $this->diskio_index . $this->diskio_descr;
    }
}
