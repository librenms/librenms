<?php

namespace App\Models;

use LibreNMS\Interfaces\Models\Keyable;

/**
 * A RADIUS authentication server a device (BNG) uses, with the counters the device reports for it.
 * Counters are null when the device does not report them, round_trip_time is in milliseconds.
 */
class RadiusServer extends DeviceRelatedModel implements Keyable
{
    protected $table = 'radius_servers';
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'address',
        'port',
        'state',
        'dead_count',
        'requests',
        'accepts',
        'rejects',
        'timeouts',
        'round_trip_time',
    ];

    protected $casts = [
        'port' => 'integer',
        'dead_count' => 'integer',
        'requests' => 'integer',
        'accepts' => 'integer',
        'rejects' => 'integer',
        'timeouts' => 'integer',
        'round_trip_time' => 'integer',
    ];

    public function getCompositeKey(): string
    {
        return "$this->address|$this->port";
    }
}
