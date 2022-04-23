<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TnmsneInfo extends Model
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
