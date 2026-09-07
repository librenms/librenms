<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class TnmsneInfo extends DeviceRelatedModel
{
    use HasFactory;

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
