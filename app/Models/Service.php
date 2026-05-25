<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Service extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $primaryKey = 'service_id';
    protected $fillable = [
        'service_id',
        'device_id',
        'service_ip',
        'service_type',
        'service_desc',
        'service_param',
        'service_ignore',
        'service_status',
        'service_changed',
        'service_message',
        'service_disabled',
        'service_ds',
        'service_template_id',
        'service_name',
    ];

    // ---- Query Scopes ----

    /**
     * Scope a query to only include active services (not ignored or disabled).
     */
    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
        ]);
    }

    /**
     * Scope a query to only include services that are in an OK state.
     */
    public function scopeIsOk(Builder $query): Builder
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
            ['service_status', '=', 0],
        ]);
    }

    /**
     * Scope a query to only include services that are in a critical state.
     */
    public function scopeIsCritical(Builder $query): Builder
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
            ['service_status', '=', 2],
        ]);
    }

    /**
     * Scope a query to only include services that are in a warning state.
     */
    public function scopeIsWarning(Builder $query): Builder
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
            ['service_status', '=', 1],
        ]);
    }

    /**
     * Scope a query to only include services that are ignored.
     */
    public function scopeIsIgnored(Builder $query): Builder
    {
        return $query->where([
            ['service_ignore', '=', 1],
            ['service_disabled', '=', 0],
        ]);
    }

    /**
     * Scope a query to only include services that are disabled.
     */
    public function scopeIsDisabled(Builder $query): Builder
    {
        return $query->where('service_disabled', 1);
    }
}
