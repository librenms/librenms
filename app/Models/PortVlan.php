<?php

namespace App\Models;

class PortVlan extends PortRelatedModel
{
    protected $table = 'ports_vlans';
    protected $primaryKey = 'port_vlan_id';
    public $timestamps = false;


    /**
     * Get the user's first name.
     *
     * @param  string  $value
     * @return string
     */
    public function getUntaggedAttribute($value)
    {
        if (! $value) {
            if($value == $this->port->ifvlan) {
                $value = 1;
            }
        }    
        return $value;
    }


    // ---- Define Relationships ----

    public function vlans()
    {
        return $this->belongsTo(\App\Models\Vlan::class, 'vlan', 'vlan_vlan')->where('device_id', $this->device_id);
    }
}
