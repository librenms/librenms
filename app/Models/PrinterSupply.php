<?php

namespace App\Models;

class PrinterSupply extends DeviceRelatedModel
{
    protected $table = 'printer_supplies';
    protected $primaryKey = 'supply_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'supply_oid',
        'supply_capacity_oid',
        'supply_index',
        'supply_type',
        'supply_descr',
        'supply_capacity',
        'supply_current',
    ];

    public function getCompositeKey()
    {
        return "$this->supply_type-$this->supply_index";
    }
}
