<?php

namespace App\Models;

use App\Observers\ServiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ObservedBy([ServiceObserver::class])]
class Service extends DeviceRelatedModel
{
    use HasFactory;

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

    public function displayName(): string
    {
        return $this->service_name ?: $this->service_type;
    }

    // ---- Query Scopes ----

    protected function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('service_type')->orderBy('service_name')->orderBy('service_id');
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsActive($query)
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsOk($query)
    {
        return $this->scopeIsActive($query)->where('service_status', 0);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsCritical($query)
    {
        return $this->scopeIsActive($query)->where('service_status', 2);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsWarning($query)
    {
        return $this->scopeIsActive($query)->where('service_status', 1);
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
