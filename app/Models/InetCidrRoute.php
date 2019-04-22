<?php

namespace App\Models;

class InetCidrRoute extends DeviceRelatedModel
{
    protected $table = 'inetCidrRoute';
//    protected $primaryKey = 'inetCidrRoute_id';
    public $timestamps = true;

    public function getPort()
    {
        return Port::query()
            ->where('device_id', '=', $this->device_id)
            ->where('ifIndex', '=', $this->inetCidrRouteIfIndex)
            ->get()->first();
    }

    // ---- Define Relationships ----
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }
}
