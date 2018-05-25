<?php

namespace App\Models;

class Service extends BaseModel
{
    const UPDATED_AT = 'service_changed';

    protected $primaryKey = 'service_id';

    protected $dateFormat = 'U';

    protected $fillable = [
        'service_type',
        'service_ip',
        'service_desc',
        'service_param',
        'service_ignore',
        'service_changed',
        'service_message',
        'service_ds'
    ];
    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    public function scopeState($query, $string)
    {
        switch ($string) {
            case 'ok':
                return $query->where('service_status', 0);
            case 'warning':
                return $query->where('service_status', 1);
            case 'critical':
                return $query->where('service_status', 2);
            default:
                throw new \Exception("Invalid string passed to Service State");
        }
    }
    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }

    // ---- Mutatiors / Accessors ----

    // Cast Service Ignored to integer
    public function setServiceIgnoreAttribute($value)
    {
        $this->attributes['service_ignore'] = (int)$value;
    }

    public function setCreatedAt($value)
    {
        // Created at fields don't exist
    }

    public function getCreatedAt($value)
    {
        // Created at fields don't exist
    }
}
