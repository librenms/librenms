<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeviceStats extends DeviceRelatedModel
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
}
