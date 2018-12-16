<?php

namespace App\Models;

class Mempool extends BaseModel
{

    protected $table = 'mempools';

    protected $primaryKey = 'mempool_id';

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    // ---- Define Relationships ----
    
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }
}
