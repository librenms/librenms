<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Interfaces\Models\Keyable;
use Permissions;

class ServiceTemplate extends Model implements Keyable
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'device_group_id',
        'ip',
        'type',
        'desc',
        'param',
        'ignore',
        'status',
        'changed',
        'disabled',
        'name',
    ];

    protected $attributes = [ // default values
        'ignore' => '0',
        'disabled' => '0',
    ];

    protected $casts = [
        'ignore' => 'integer',
        'disabled' => 'integer',
    ];

    // ---- Helper Functions ----

    public function getCompositeKey()
    {
        return $this->id . '-' . $this->device_group_id;
    }

    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        return $query->whereIn('id', Permissions::serviceTemplatesForUser($user));
    }

    /**
     * Check if user can access this device.
     *
     * @param  User $user
     * @return bool
     */
    public function canAccess($user)
    {
        if (! $user) {
            return false;
        }

        if ($user->hasGlobalRead()) {
            return true;
        }

        return Permissions::canAccessServiceTemplate($this->id, $user->user_id);
    }

    /**
     * @param  Builder $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where('disabled', 1);
    }

    public function users()
    {
        // FIXME does not include global read
        return $this->belongsToMany(\App\Models\User::class, 'service_templates_perms', 'service_template_id', 'user_id');
    }

    public function groups()
    {
        return $this->belongsToMany(\App\Models\DeviceGroup::class, 'service_template_device_group', 'service_template_id', 'device_group_id');
    }
}
