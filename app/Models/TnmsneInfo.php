<?php

namespace App\Models;

class TnmsneInfo extends DeviceRelatedModel
{
    protected $table = 'tnmsneinfo';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'neID',
        'neType',
        'neName',
        'neLocation',
        'neAlarm',
        'neOpMode',
        'neOpState',
    ];
}
