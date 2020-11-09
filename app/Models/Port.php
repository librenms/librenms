<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use LibreNMS\Util\Rewrite;
use Permissions;

class Port extends DeviceRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'port_id';

    /**
     * Initialize this class
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function (Port $port) {
            // delete related data
            $port->adsl()->delete();
            $port->fdbEntries()->delete();
            $port->ipv4()->delete();
            $port->ipv6()->delete();
            $port->macAccounting()->delete();
            $port->macs()->delete();
            $port->nac()->delete();
            $port->ospfNeighbors()->delete();
            $port->ospfPorts()->delete();
            $port->pseudowires()->delete();
            $port->statistics()->delete();
            $port->stp()->delete();
            $port->vlans()->delete();

            // dont have relationships yet
            DB::table('juniAtmVp')->where('port_id', $port->port_id)->delete();
            DB::table('ports_perms')->where('port_id', $port->port_id)->delete();
            DB::table('links')->where('local_port_id', $port->port_id)->orWhere('remote_port_id', $port->port_id)->delete();
            DB::table('ports_stack')->where('port_id_low', $port->port_id)->orWhere('port_id_high', $port->port_id)->delete();

            \Rrd::purge(optional($port->device)->hostname, \Rrd::portName($port->port_id)); // purge all port rrd files
        });
    }

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

        foreach ((array) \LibreNMS\Config::get('rewrite_if', []) as $src => $val) {
            if (Str::contains(strtolower($label), strtolower($src))) {
                $label = $val;
            }
        }

        foreach ((array) \LibreNMS\Config::get('rewrite_if_regexp', []) as $reg => $val) {
            $label = preg_replace($reg . 'i', $val, $label);
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
     * @param User|int $user
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

        return Permissions::canAccessDevice($this->device_id, $user) || Permissions::canAccessPort($this->port_id, $user);
    }

    // ---- Accessors/Mutators ----

    public function getIfPhysAddressAttribute($mac)
    {
        if (! empty($mac)) {
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
            ['ifOperStatus', '!=', 'up'],
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

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsValid($query)
    {
        return $query->where([
            ['deleted', '=', 0],
            ['disabled', '=', 0],
        ]);
    }

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasPortAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function adsl()
    {
        return $this->hasMany(PortAdsl::class, 'port_id');
    }

    public function events()
    {
        return $this->morphMany(Eventlog::class, 'events', 'type', 'reference');
    }

    public function fdbEntries()
    {
        return $this->hasMany(\App\Models\PortsFdb::class, 'port_id', 'port_id');
    }

    public function ipv4()
    {
        return $this->hasMany(\App\Models\Ipv4Address::class, 'port_id');
    }

    public function ipv6()
    {
        return $this->hasMany(\App\Models\Ipv6Address::class, 'port_id');
    }

    public function macAccounting()
    {
        return $this->hasMany(MacAccounting::class, 'port_id');
    }

    public function macs()
    {
        return $this->hasMany(Ipv4Mac::class, 'port_id');
    }

    public function nac()
    {
        return $this->hasMany(PortsNac::class, 'port_id');
    }

    public function ospfNeighbors()
    {
        return $this->hasMany(OspfNbr::class, 'port_id');
    }

    public function ospfPorts()
    {
        return $this->hasMany(OspfPort::class, 'port_id');
    }

    public function pseudowires()
    {
        return $this->hasMany(Pseudowire::class, 'port_id');
    }

    public function statistics()
    {
        return $this->hasMany(PortStatistic::class, 'port_id');
    }

    public function stp()
    {
        return $this->hasMany(PortStp::class, 'port_id');
    }

    public function users()
    {
        // FIXME does not include global read
        return $this->belongsToMany(\App\Models\User::class, 'ports_perms', 'port_id', 'user_id');
    }

    public function vlans()
    {
        return $this->hasMany(PortVlan::class, 'port_id');
    }
}
