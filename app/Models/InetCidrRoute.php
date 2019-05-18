<?php

namespace App\Models;

class InetCidrRoute extends DeviceRelatedModel
{
    protected $table = 'inetCidrRoute';
    protected $primaryKey = 'inetCidrRoute_id';
    public $timestamps = true;

    // ---- Define Relationships ----
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }

    public function port()
    {
        return $this->belongsTo('App\Models\Port', 'port_id', 'port_id');
    }
}
