<?php

namespace App\Models;

class TnmsNeInfo extends DeviceRelatedModel
{
    protected $primaryKey = 'tnmsne_info_id';
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

    // ---- Define Relationships ----

    public function alarms()
    {
        return $this->hasMany('App\Models\TnmsAlarm', 'tnmsne_info_id');
    }
}
