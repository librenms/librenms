<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use LibreNMS\Interfaces\Models\Keyable;

class Ipv4Mac extends PortRelatedModel implements Keyable
{
    protected $table = 'ipv4_mac';
    public $timestamps = false;
    protected $fillable = [
        'port_id',
        'device_id',
        'mac_address',
        'ipv4_address',
        'context_name',
    ];

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Port, $this>
     */
    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_id');
    }

    // Ports in NMS with a matching MAC address and IP address.
    // This can match multiple ports if you have multiple sub-interfaces with the same
    // IP address (e.g. different VRFs, or mutiple point to point links on Mikrotik)
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\Port, Ipv4Mac, $this>
     */
    public function remote_ports_maybe(): HasManyThrough
    {
        // Join onto this class first because we need both the mac_address and ipv4_address columns
        return $this->hasManyThrough(Port::class, Ipv4Mac::class, 'id', 'ifPhysAddress', 'id', 'mac_address')
            ->join('ipv4_addresses', function ($j) {
                $j->on('ipv4_mac.ipv4_address', 'ipv4_addresses.ipv4_address');
                $j->on('ports.port_id', 'ipv4_addresses.port_id');
            })
            ->whereNotIn('mac_address', ['000000000000', 'ffffffffffff']);
    }

    public function getCompositeKey(): string
    {
        return $this->getAttribute('port_id') . '_' . $this->getAttribute('ipv4_address');
    }
}
