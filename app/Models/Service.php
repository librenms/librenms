<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use App\Observers\ServiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;

#[ObservedBy([\App\Observers\ServiceObserver::class])]
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
     * @param  Builder  $query
     * @return Builder
     */
    #[Scope]
    protected function isActive($query)
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
    #[Scope]
    protected function isOk($query)
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
    #[Scope]
    protected function isCritical($query)
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
    #[Scope]
    protected function isWarning($query)
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
    #[Scope]
    protected function isIgnored($query)
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
    #[Scope]
    protected function isDisabled($query)
    {
        return $query->where('service_disabled', 1);
    }
}
