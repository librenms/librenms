<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LibreNMS\Interfaces\Models\Keyable;

/**
 * Subscriber sessions that are up, by session type, for the device (port_id null) or an access port
 */
class SubscriberCount extends DeviceRelatedModel implements Keyable
{
    /**
     * Session types and their names, the order is the display order. 'total' is the device total
     * when the device reports one, the total of a port is the sum of its session types.
     */
    public const array SESSION_TYPES = [
        'total' => 'Total',
        'pppoe' => 'PPPoE',
        'ppp' => 'PPP',
        'ipoe' => 'IPoE',
        'l2tp' => 'L2TP',
        'other' => 'Other',
    ];

    protected $table = 'subscriber_counts';
    public $timestamps = false;

    protected $fillable = [
        'device_id',
        'port_id',
        'session_type',
        'sessions',
    ];

    protected $casts = [
        'sessions' => 'integer',
    ];

    public function getCompositeKey(): string
    {
        return "$this->port_id|$this->session_type";
    }

    /**
     * @return BelongsTo<Port, $this>
     */
    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_id', 'port_id');
    }
}
