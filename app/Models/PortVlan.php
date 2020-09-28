<?php

namespace App\Models;

class PortVlan extends PortRelatedModel
{
    protected $table = 'ports_vlans';
    protected $primaryKey = 'port_vlan_id';
    public $timestamps = false;

    // ---- Define Relationships ----

    public function vlan1()
    {
        return $this->belongsTo(\App\Models\Vlan::class, 'vlan', 'vlan_vlan');
    }
}
