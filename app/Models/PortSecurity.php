<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

class PortSecurity extends DeviceRelatedModel implements Keyable
{
    use HasFactory;

    protected $table = 'port_security';
    // protected $primaryKey = 'port_id';
    public $timestamps = false;
    protected $fillable = [
        'port_id',
        'device_id',
        'cpsIfPortSecurityEnable',
        'cpsIfPortSecurityStatus',
        'cpsIfMaxSecureMacAddr',
        'cpsIfCurrentSecureMacAddrCount',
        'cpsIfViolationAction',
        'cpsIfViolationCount',
        'cpsIfSecureLastMacAddress',
        'cpsIfStickyEnable',
        'cpsIfSecureLastMacAddrVlanId',
    ];

    public function getCompositeKey()
    {
        return $this->port_id;
    }
}
