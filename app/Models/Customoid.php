<?php

namespace App\Models;

class Customoid extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $primaryKey = 'customoid_id';

    protected $fillable = [
        'device_id',
        'customoid_descr',
        'customoid_oid',
        'customoid_datatype',
        'customoid_unit',
        'customoid_divisor',
        'customoid_multiplier',
        'customoid_limit',
        'customoid_limit_warn',
        'customoid_limit_low',
        'customoid_limit_low_warn',
        'customoid_alert',
        'customoid_passed',
        'user_func',
    ];
}
