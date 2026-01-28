<?php

namespace App\Models;

use App\Casts\BytesMutatesRate;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

class MacAccounting extends PortRelatedModel implements Keyable
{
    protected $table = 'mac_accounting';
    protected $primaryKey = 'ma_id';
    public $timestamps = false;
    protected $casts = [
        'bytes_in' => BytesMutatesRate::class,
        'bytes_out' => BytesMutatesRate::class,
    ];
    protected $fillable = [
        'port_id',
        'mac',
        'ifIndex',
        'vlan',
        'bps_in',
        'bps_out',
        'bytes_in',
        'bytes_out',
        'packets_in',
        'packets_out',
        'last_polled',
    ];

    /**
     * @inheritDoc
     */
    public function getCompositeKey(): string
    {
        return $this->getAttribute('ifIndex') . '-' . $this->getAttribute('mac');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
