<?php

namespace App\Models;

class Service extends BaseModel
{
    public $timestamps = false;
    protected $primaryKey = 'service_id';

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }
}
