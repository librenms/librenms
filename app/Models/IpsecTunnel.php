<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpsecTunnel extends Model
{
    protected $table = 'ipsec_tunnels';

    protected $primaryKey = 'tunnel_id';

    public $timestamps = false;

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }
}
