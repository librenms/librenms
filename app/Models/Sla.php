<?php

namespace App\Models;

class Sla extends DeviceRelatedModel
{
    protected $table = 'slas';
    protected $primaryKey = 'sla_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'sla_nr',
        'owner',
        'tag',
        'rtt_type',
        'status',
        'opstatus',
        'deleted',
    ];
}
