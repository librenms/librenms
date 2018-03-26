<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'port_id';

    // ---- Helper Functions ----

    /**
     * Returns a human readable label for this port
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->ifName) {
            return $this->ifName;
        }

        if ($this->ifDescr) {
            return $this->ifDescr;
        }

        return $this->ifIndex;
    }

    // ---- Accessors/Mutators ----

    public function getIfPhysAddressAttribute($mac)
    {
        if (!empty($mac)) {
            return preg_replace('/(..)(..)(..)(..)(..)(..)/', '\\1:\\2:\\3:\\4:\\5:\\6', $mac);
        }
        return null;
    }

    // ---- Query scopes ----

    public function scopeNotDeleted($query)
    {
        return $query->where([
            ['deleted', '=', 0],
        ]);
    }

    public function scopeIsUp($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 0],
            ['ifOperStatus', '=', 'up'],
        ]);
    }

    public function scopeIsDown($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 0],
            ['ifOperStatus', '=', 'down'],
            ['ifAdminStatus', '=', 'up'],
        ]);
    }

    public function scopeIsIgnored($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 1],
        ]);
    }

    public function scopeIsDisabled($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 0],
            ['ifAdminStatus', '=', 'down'],
        ]);
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }

    public function users()
    {
        // FIXME does not include global read
        return $this->belongsToMany('App\Models\User', 'ports_perms', 'port_id', 'user_id');
    }

    public function ipv4()
    {
        return $this->hasMany('App\Models\General\IPv4', 'port_id');
    }

    public function ipv6()
    {
        return $this->hasMany('App\Models\General\IPv6', 'port_id');
    }
}
