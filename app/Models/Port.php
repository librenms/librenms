<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ports';
    /**
     * The primary key column name.
     *
     * @var string
     */
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

    /**
     * Get the device this port belongs to.
     *
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id', 'device_id');
    }

    /**
     * Returns a list of users that can access this port.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'ports_perms', 'port_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function ipv4()
    {
        return $this->hasMany('App\Models\General\IPv4', 'port_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function ipv6()
    {
        return $this->hasMany('App\Models\General\IPv6', 'port_id');
    }
}
