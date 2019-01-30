<?php

namespace App\Models;

class TnmsNeInfo extends DeviceRelatedModel
{
    protected $table = 'tnmsneinfo';
    public $timestamps = false;

    // ---- Define Relationships ----

    public function alarms()
    {
        return $this->hasMany('App\Models\TnmsAlarm', 'tnmsne_info_id', 'tnmsne_info_id');
    }
}
