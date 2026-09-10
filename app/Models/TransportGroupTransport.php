<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportGroupTransport extends Model
{
    protected $table = 'transport_group_transport';
    public $timestamps = false;

    public function transportGroup(): BelongsTo
    {
        return $this->belongsTo(AlertTransportGroup::class, 'transport_group_id');
    }

    public function transport(): BelongsTo
    {
        return $this->belongsTo(AlertTransport::class, 'transport_id');
    }
}
