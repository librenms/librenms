<?php

namespace App\Models;

class Vminfo extends DeviceRelatedModel
{
    protected $table = 'vminfo';
    public $timestamps = false;

    // ---- Define Relationships ----
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }

}
