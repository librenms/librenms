<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'user_id';
    protected $fillable = ['realname', 'username', 'email', 'level', 'descr', 'can_modify_passwd'];
    protected $hidden = ['password', 'remember_token', 'pivot'];

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

    // ---- Define Relationships ----

    public function devices()
    {
        if ($this->hasGlobalRead()) {
//            $instance = $this->newRelatedInstance('App\Models\Device');
//            return new HasAll($instance);
            return Device::query();
        } else {
            return $this->belongsToMany('App\Models\Device', 'devices_perms', 'user_id', 'device_id');
        }
    }

    public function ports()
    {
        if ($this->hasGlobalRead()) {
            return Port::query();
        } else {
            //FIXME we should return all ports for a device if the user has been given access to the whole device.
            return $this->belongsToMany('App\Models\Port', 'ports_perms', 'user_id', 'port_id');
        }
    }

    public function dashboards()
    {
        return $this->hasMany('App\Models\Dashboard', 'user_id');
    }

    public function widgets()
    {
        return $this->hasMany('App\Models\UsersWidgets', 'user_id');
    }
}
