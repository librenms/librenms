<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Service extends BaseModel
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

    protected $attributes = [ // default values
        'ignore' => '0',
        'disabled' => '0',
    ];

    protected $casts = [
        'ignore' => 'integer',
        'disabled' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
    }

    // ---- Query Scopes ----

    /**
     * @param  Builder  $query
     * @param  User  $user
     * @return Builder
     */
    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        //return $query->whereIn('id', Permissions::deviceGroupsForUser($user));
    }

    /**
     * @param Builder $query
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
     * @param Builder $query
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
     * @param Builder $query
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
     * @param Builder $query
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
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where('service_disabled', 1);
    }

    // ---- Define Relationships ----

    public function devices()
    {
        return $this->belongsToMany(\App\Models\Device::class, 'services', 'service_id', 'device_id');
    }
}
