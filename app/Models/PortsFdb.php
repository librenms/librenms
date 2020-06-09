<?php

namespace App\Models;

class PortsFdb extends PortRelatedModel
{
    protected $table = 'ports_fdb';
    protected $primaryKey = 'ports_fdb_id';
    public $timestamps = true;

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id', 'device_id');
    }

    public function vlan()
    {
        return $this->belongsTo(\App\Models\Vlan::class, 'vlan_id', 'vlan_id');
    }
}
