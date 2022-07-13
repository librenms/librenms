<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ipv4Mac extends PortRelatedModel
{
    protected $table = 'ipv4_mac';
    public $timestamps = false;

    // ---- Define Relationships ----

    public function device(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id');
    }
}
