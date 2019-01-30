<?php

namespace App\Models;

class TnmsAlarm extends DeviceRelatedModel
{
    public $timestamps = false;

    // ---- Define Relationships ----

    public function ne()
    {
        return $this->belongsTo('App\Models\TnmsNeInfo', 'tnmsne_info_id');
    }
}

