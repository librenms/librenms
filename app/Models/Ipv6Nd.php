<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class Ipv6Nd extends PortRelatedModel implements Keyable
{
    use HasFactory;
    protected $table = 'ipv6_nd';
    protected $fillable = [
        'port_id',
        'device_id',
        'mac_address',
        'ipv6_address',
        'context_name',
    ];

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    public function getCompositeKey(): string
    {
        return $this->getAttribute('port_id') . '_' . $this->getAttribute('ipv6_address');
    }
}
