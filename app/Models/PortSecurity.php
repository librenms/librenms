<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'port_security_enable',
        'status',
        'max_addresses',
        'address_count',
        'violation_action',
        'violation_count',
        'last_mac_address',
        'sticky_enable',
    ];

    public function getCompositeKey()
    {
        return $this->port_id;
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
