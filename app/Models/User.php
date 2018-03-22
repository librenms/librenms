<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'realname', 'username', 'password', 'email', 'level', 'descr',
    ];
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pivot',
    ];


    // ---- Helper Functions ----

    /**
     * Test if this user has global read access
     * these users have a level of 5, 10 or 11 (demo).
     *
     * @return boolean
     */
    public function hasGlobalRead()
    {
        return $this->isAdmin() || $this->level == 5;
    }

    /**
     * Test if the User is an admin or demo.
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->level >= 10;
    }

    /**
     * Check if this user has access to a device
     *
     * @param Device|int $device can be a device Model or device id
     * @return bool
     */
    public function canAccessDevice($device)
    {
        return $this->hasGlobalRead() || $this->devices->contains($device);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->user_id;
        // TODO: Implement getJWTIdentifier() method.
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // TODO: Implement getJWTCustomClaims() method.
        return ['app' => 'LibreNMS', 'username' => $this->username];
    }

    // ---- Accessors/Mutators ----

    /**
     * Encrypt passwords before saving
     *
     * @param $password
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    // ---- Define Relationships ----

    /**
     * Returns a list of devices this user has access to
     */
    public function devices()
    {
        if ($this->hasGlobalRead()) {
            return Device::query();
        } else {
            return $this->belongsToMany('App\Models\Device', 'devices_perms', 'user_id', 'device_id');
        }
    }

    /**
     * Returns a list of ports this user has access to
     */
    public function ports()
    {
        if ($this->hasGlobalRead()) {
            return Port::query();
        } else {
            //FIXME we should return all ports for a device if the user has been given access to the whole device.
            return $this->belongsToMany('App\Models\Port', 'ports_perms', 'user_id', 'port_id');
        }
    }

    /**
     * Returns a list of dashboards this user has
     */
    public function dashboards()
    {
        return $this->hasMany('App\Models\Dashboard', 'user_id');
    }

    /**
     * Returns a list of dashboards this user has
     */
    public function widgets()
    {
        return $this->hasMany('App\Models\UsersWidgets', 'user_id');
    }
}
