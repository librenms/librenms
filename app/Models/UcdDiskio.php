<?php

namespace App\Models;

class UcdDiskio extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $primaryKey = 'diskio_id';
    protected $table = 'ucd_diskio';
}
