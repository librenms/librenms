<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class PortStp extends PortRelatedModel implements Keyable
{
    protected $table = 'ports_stp';
    protected $primaryKey = 'port_stp_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'vlan',
        'port_id',
        'port_index',
        'priority',
        'state',
        'enable',
        'pathCost',
        'designatedRoot',
        'designatedCost',
        'designatedBridge',
        'designatedPort',
        'forwardTransitions',
    ];

    public function getCompositeKey()
    {
        return "$this->vlan-$this->port_index";
    }

    // ---- Define Relationships ----

    public function device(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id', 'device_id');
    }
}
