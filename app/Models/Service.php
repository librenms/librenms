<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\Clean;

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
        'service_status',
        'service_changed',
        'service_message',
        'service_ignore',
        'service_disabled',
        'service_ds',
        'service_template_id',
        'service_name',
    ];
    protected $casts = [
        'service_ignore' => 'boolean',
        'service_disabled' => 'boolean',
        'service_ds' => 'array',
        'service_template_id' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function (Service $service) {
            $service->service_ip = $service->service_ip ?? $service->device->overwrite_ip ?: $service->device->hostname;
            $service->service_message = $service->service_message ?? 'Service not yet checked';
        });

        static::saving(function (Service $service) {
            $service->service_ignore = $service->service_ignore ?? 0;
            $service->service_disabled = $service->service_disabled ?? 0;
            $service->service_template_id = $service->service_template_id ?? 0;
            $service->service_param = $service->service_param ?? [];
            $service->service_changed = time();
        });
    }

    // ---- Accessors/Mutators ----

    public function getServiceTypeAttribute(): string
    {
        return Clean::fileName($this->attributes['service_type']);
    }

    /**
     * @return  string|array  string legacy, array modern
     */
    public function getServiceParamAttribute()
    {
        return json_decode($this->attributes['service_param'], true) ?? $this->attributes['service_param'];
    }

    /**
     * @param  string|array  $service_param string legacy, array modern
     * @return void
     */
    public function setServiceParamAttribute($service_param)
    {
        $this->attributes['service_param'] = is_array($service_param) ? json_encode($service_param) : $service_param;
    }

    // ---- Query Scopes ----

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsOk($query)
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
            ['service_status', '=', 0],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsCritical($query)
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
            ['service_status', '=', 2],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsWarning($query)
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
            ['service_status', '=', 1],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsIgnored($query)
    {
        return $query->where([
            ['service_ignore', '=', 1],
            ['service_disabled', '=', 0],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where('service_disabled', 1);
    }
}
