<?php

namespace App\Models;

class PortsFdb extends BaseModel
{
    protected $table = 'ports_fdb';
    protected $primaryKey = 'ports_fdb_id';
    public $timestamps = false;

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasPortAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }

    public function port()
    {
        return $this->belongsTo('App\Models\Port', 'port_id', 'port_id');
    }

    public function vlan()
    {
        return $this->belongsTo('App\Models\Vlan', 'vlan_id', 'vlan_id');
    }
}
