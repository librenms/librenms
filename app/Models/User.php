<?php

namespace App\Models;

use App\Events\UserCreated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use LibreNMS\Authentication\LegacyAuth;
use Permissions;

/**
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 */
class User extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $primaryKey = 'user_id';
    protected $fillable = ['realname', 'username', 'email', 'level', 'descr', 'can_modify_passwd', 'auth_type', 'auth_id', 'enabled'];
    protected $hidden = ['password', 'remember_token', 'pivot'];
    protected $attributes = [ // default values
        'descr' => '',
        'realname' => '',
        'email' => '',
    ];
    protected $dispatchesEvents = [
        'created' => UserCreated::class,
    ];

    protected $casts = [
        'realname' => 'string',
        'descr' => 'string',
        'email' => 'string',
        'can_modify_passwd' => 'integer',
    ];

    // ---- Helper Functions ----

    /**
     * Test if this user has global read access
     * these users have a level of 5, 10 or 11 (demo).
     *
     * @return bool
     */
    public function hasGlobalRead()
    {
        return $this->hasGlobalAdmin() || $this->level == 5;
    }

    /**
     * Test if this user has global admin access
     * these users have a level of 10 or 11 (demo).
     *
     * @return bool
     */
    public function hasGlobalAdmin()
    {
        return $this->level >= 10;
    }

    /**
     * Test if the User is an admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->level == 10;
    }

    /**
     * Test if this user is the demo user
     *
     * @return bool
     */
    public function isDemo()
    {
        return $this->level == 11;
    }

    /**
     * Check if this user has access to a device
     *
     * @param Device|int $device can be a device Model or device id
     * @return bool
     */
    public function canAccessDevice($device)
    {
        return $this->hasGlobalRead() || Permissions::canAccessDevice($device, $this->user_id);
    }

    /**
     * Helper function to hash passwords before setting
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->attributes['password'] = $password ? Hash::make($password) : null;
    }

    /**
     * Check if the given user can set the password for this user
     *
     * @param User $user
     * @return bool
     */
    public function canSetPassword($user)
    {
        if ($user && LegacyAuth::get()->canUpdatePasswords()) {
            if ($user->isAdmin()) {
                return true;
            }

            return $user->is($this) && $this->can_modify_passwd;
        }

        return false;
    }

    // ---- Query scopes ----

    /**
     * This restricts the query to only users that match the current auth method
     * It is not needed when using user_id, but should be used for username and auth_id
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeThisAuth($query)
    {
        // find user including ones where we might not know the auth type
        $type = LegacyAuth::getType();

        return $query->where(function ($query) use ($type) {
            $query->where('auth_type', $type)
                ->orWhereNull('auth_type')
                ->orWhere('auth_type', '');
        });
    }

    public function scopeAdminOnly($query)
    {
        $query->where('level', 10);
    }

    // ---- Accessors/Mutators ----

    public function setRealnameAttribute($realname)
    {
        $this->attributes['realname'] = (string) $realname;
    }

    public function setDescrAttribute($descr)
    {
        $this->attributes['descr'] = (string) $descr;
    }

    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = (string) $email;
    }

    public function setCanModifyPasswdAttribute($modify)
    {
        $this->attributes['can_modify_passwd'] = $modify ? 1 : 0;
    }

    public function setEnabledAttribute($enable)
    {
        $this->attributes['enabled'] = $enable ? 1 : 0;
    }

    public function getDevicesAttribute()
    {
        // pseudo relation
        if (! array_key_exists('devices', $this->relations)) {
            $this->setRelation('devices', $this->devices()->get());
        }

        return $this->getRelation('devices');
    }

    // ---- Define Relationships ----

    public function apiToken(): HasOne
    {
        return $this->hasOne(\App\Models\ApiToken::class, 'user_id', 'user_id');
    }

    public function devices()
    {
        // pseudo relation
        return Device::query()->when(! $this->hasGlobalRead(), function ($query) {
            return $query->whereIn('device_id', Permissions::devicesForUser($this));
        });
    }

    public function deviceGroups(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\DeviceGroup::class, 'devices_group_perms', 'user_id', 'device_group_id');
    }

    public function ports()
    {
        if ($this->hasGlobalRead()) {
            return Port::query();
        } else {
            //FIXME we should return all ports for a device if the user has been given access to the whole device.
            return $this->belongsToMany(\App\Models\Port::class, 'ports_perms', 'user_id', 'port_id');
        }
    }

    public function dashboards(): HasMany
    {
        return $this->hasMany(\App\Models\Dashboard::class, 'user_id');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(\App\Models\UserPref::class, 'user_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(\App\Models\UserWidget::class, 'user_id');
    }
}
