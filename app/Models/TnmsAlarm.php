<?php

namespace App\Models;

class TnmsAlarm extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $fillable = [
        'tnmsne_info_id',
        'device_id',
        'alarm_num',
        'alarm_cause',
        'alarm_location',
        'neAlarmtimestamp',
    ];

    // ---- Define Relationships ----

    public function ne()
    {
        return $this->belongsTo('App\Models\TnmsNeInfo', 'tnmsne_info_id');
    }
}

