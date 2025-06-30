<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class PortVlan extends PortRelatedModel implements Keyable
{
    protected $table = 'ports_vlans';
    protected $primaryKey = 'port_vlan_id';
    public $timestamps = false;
    protected $fillable = [
        'port_vlan_id',
        'device_id',
        'port_id',
        'vlan',
        'baseport',
        'priority',
        'state',
        'cost',
        'untagged',
    ];

    public function getUntaggedAttribute($value)
    {
        if (! $value) {
            if ($this->port && $this->vlan == $this->port->ifVlan) {
                $value = 1;
            }
        }

        return $value;
    }

    public function getCompositeKey()
    {
        return $this->port_id . '-' . $this->vlan;
    }
}
