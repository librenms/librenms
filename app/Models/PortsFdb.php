<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortsFdb extends PortRelatedModel
{
    protected $table = 'ports_fdb';
    protected $primaryKey = 'ports_fdb_id';
    public $timestamps = true;

    // ---- Define Relationships ----

    public function device(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id', 'device_id');
    }

    public function vlan(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Vlan::class, 'vlan_id', 'vlan_id');
    }
}
