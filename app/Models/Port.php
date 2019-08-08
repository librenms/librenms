<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\Rewrite;

class Port extends DeviceRelatedModel
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
        $os = $this->device->os;

        if (\LibreNMS\Config::getOsSetting($os, 'ifname')) {
            $label = $this->ifName;
        } elseif (\LibreNMS\Config::getOsSetting($os, 'ifalias')) {
            $label = $this->ifAlias;
        }

        if (empty($label)) {
            $label = $this->ifDescr;

            if (\LibreNMS\Config::getOsSetting($os, 'ifindex')) {
                $label .= " $this->ifIndex";
            }
        }

        foreach ((array)\LibreNMS\Config::get('rewrite_if', []) as $src => $val) {
            if (str_i_contains($label, $src)) {
                $label = $val;
            }
        }

        foreach ((array)\LibreNMS\Config::get('rewrite_if_regexp', []) as $reg => $val) {
            $label = preg_replace($reg.'i', $val, $label);
        }

        return $label;
    }

    /**
     * Get the shortened label for this device.  Replaces things like GigabitEthernet with GE.
     *
     * @return string
     */
    public function getShortLabel()
    {
        return Rewrite::shortenIfName(Rewrite::normalizeIfName($this->ifName ?: $this->ifDescr));
    }

    /**
     * Check if user can access this port.
     *
     * @param User $user
     * @return bool
     */
    public function canAccess($user)
    {
        if (!$user) {
            return false;
        }

        if ($user->hasGlobalRead()) {
            return true;
        }

        $port_query = DB::table('ports_perms')
            ->where('user_id', $user->user_id)
            ->where('port_id', $this->port_id);

        $device_query = DB::table('devices_perms')
            ->where('user_id', $user->user_id)
            ->where('device_id', $this->device_id);

        return $port_query->union($device_query)->exists();
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

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsDeleted($query)
    {
        return $query->where([
            ['deleted', 1],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsNotDeleted($query)
    {
        return $query->where([
            ['deleted', 0],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsUp($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 0],
            ['disabled', '=', 0],
            ['ifOperStatus', '=', 'up'],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsDown($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 0],
            ['disabled', '=', 0],
            ['ifOperStatus', '=', 'down'],
            ['ifAdminStatus', '=', 'up'],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsShutdown($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 0],
            ['disabled', '=', 0],
            ['ifAdminStatus', '=', 'down'],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsIgnored($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 1],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['disabled', '=', 1],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeHasErrors($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['ignore', '=', 0],
            ['disabled', '=', 0],
        ])->where(function ($query) {
            /** @var Builder $query */
            $query->where('ifInErrors_delta', '>', 0)
                ->orWhere('ifOutErrors_delta', '>', 0);
        });
    }

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasPortAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function events()
    {
        return $this->morphMany(Eventlog::class, 'events', 'type', 'reference');
    }

    public function fdbEntries()
    {
        return $this->hasMany('App\Models\PortsFdb', 'port_id', 'port_id');
    }

    public function users()
    {
        // FIXME does not include global read
        return $this->belongsToMany('App\Models\User', 'ports_perms', 'port_id', 'user_id');
    }

    public function ipv4()
    {
        return $this->hasMany('App\Models\Ipv4Address', 'port_id');
    }

    public function ipv6()
    {
        return $this->hasMany('App\Models\Ipv6Address', 'port_id');
    }
}
