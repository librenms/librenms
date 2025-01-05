<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Link extends Model
{
    use HasFactory;

    public $timestamps = false;

    // ---- Define Relationships ----

    public function device(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Device::class, 'local_device_id', 'device_id');
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Port::class, 'local_port_id', 'port_id');
    }

    public function remoteDevice(): HasOne
    {
        return $this->hasOne(\App\Models\Device::class, 'device_id', 'remote_device_id');
    }

    public function remotePort(): HasOne
    {
        return $this->hasOne(\App\Models\Port::class, 'port_id', 'remote_port_id');
    }
}
