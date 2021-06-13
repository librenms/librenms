<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class PrinterSupply extends DeviceRelatedModel implements Keyable
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
