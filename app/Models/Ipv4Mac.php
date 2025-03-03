<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ipv4Mac extends PortRelatedModel
{
    protected $table = 'ipv4_mac';
    public $timestamps = false;

    // ---- Define Relationships ----

    public function device(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id');
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_id');
    }

    // Ports in NMS with a matching MAC address and IP address.
    // This can match multiple ports if you have multiple sub-interfaces with the same
    // IP address (e.g. different VRFs, or mutiple point to point links on Mikrotik)
    public function remote_ports_maybe(): BelongsToMany
    {
        return $this->belongsToMany(Port::class, 'view_port_mac_links', 'ipv4_mac_id', 'remote_port_id');
    }
}
