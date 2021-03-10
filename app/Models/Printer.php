<?php

namespace App\Models;

class Printer extends DeviceRelatedModel
{
    protected $table = 'toner';
    protected $primaryKey = 'toner_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'toner_oid',
        'toner_capacity_oid',
        'toner_index',
        'toner_type',
        'toner_descr',
        'toner_capacity',
        'toner_current',
    ];

    public function getCompositeKey()
    {
        return "$this->toner_type-$this->toner_index";
    }
}
