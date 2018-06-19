<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vlan extends Model
{
    protected $primaryKey = 'vlan_id';

    public $timestamps = false;

    protected $fillable = [
        'vlan_vlan',
        'vlan_domain',
        'vlan_name',
        'vlan_type',
        'vlan_mtu'
    ];

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }
}
