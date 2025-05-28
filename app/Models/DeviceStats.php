<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceStats extends Model
{
    use HasFactory;
    protected $fillable = [
        'device_id',
        'ping_last_timestamp',
        'ping_rtt_last',
        'ping_rtt_prev',
        'ping_rtt_avg',
        'ping_loss_last',
        'ping_loss_prev',
        'ping_loss_avg',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
