<?php

namespace App\Models;

class PrinterSupply extends DeviceRelatedModel
{
    protected $table = 'printer_supplies';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'printer_oid',
        'printer_capacity_oid',
        'printer_index',
        'printer_type',
        'printer_descr',
        'printer_capacity',
        'printer_current',
    ];

    public function getCompositeKey()
    {
        return "$this->printer_type-$this->printer_index";
    }
}
