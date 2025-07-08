<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Vlan extends DeviceRelatedModel
{
    protected $primaryKey = 'vlan_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'vlan_vlan',
        'vlan_domain',
        'vlan_name',
        'vlan_type',
        'vlan_mtu',
    ];

    public function ports(): HasMany
    {
        return $this->hasMany(PortVlan::class, 'vlan', 'vlan_vlan');
    }

    public function getCompositeKey(): string
    {
        return $this->vlan_vlan . '-' . $this->vlan_domain;
    }
}
