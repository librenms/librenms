<?php

namespace App\Models;

class Ipv4Mac extends PortRelatedModel
{
    protected $table = 'ipv4_mac';
    public $timestamps = false;

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }
}
