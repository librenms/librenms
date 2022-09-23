<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

class Stp extends DeviceRelatedModel implements Keyable
{
    protected $table = 'stp';
    protected $primaryKey = 'stp_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'vlan',
        'rootBridge',
        'bridgeAddress',
        'protocolSpecification',
        'priority',
        'timeSinceTopologyChange',
        'topChanges',
        'designatedRoot',
        'rootCost',
        'rootPort',
        'maxAge',
        'helloTime',
        'holdTime',
        'forwardDelay',
        'bridgeMaxAge',
        'bridgeHelloTime',
        'bridgeForwardDelay',
    ];

    public function getCompositeKey()
    {
        return $this->vlan;
    }
}
