<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class PortStp extends PortRelatedModel implements Keyable
{
    protected $table = 'ports_stp';
    protected $primaryKey = 'port_stp_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'port_id',
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
        return $this->port_id; // one per-port
    }
}
