<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stp extends Model
{
    protected $table = 'stp';
    protected $primaryKey = 'stp_id';
    public $timestamps = false;
    protected $fillable = [
        'device_id',
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
}
