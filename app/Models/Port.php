<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
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
            $port->vdsl()->delete();
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
            $port->links()->delete();
            $port->remoteLinks()->delete();

            // dont have relationships yet
            DB::table('juniAtmVp')->where('port_id', $port->port_id)->delete();
            DB::table('ports_perms')->where('port_id', $port->port_id)->delete();
            DB::table('ports_stack')->where('low_port_id', $port->port_id)->orWhere('high_port_id', $port->port_id)->delete();

            \Rrd::purge($port->device?->hostname, \Rrd::portName($port->port_id)); // purge all port rrd files
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
        $os = $this->device?->os;

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
     * Get a label containing both the ifName and ifAlias if they differ.
     */
    public function getFullLabel(): string
    {
        $label = $this->getLabel();

        if ($label == $this->ifAlias || empty($this->ifAlias)) {
            return $label;
        }

        return "$label - $this->ifAlias";
    }

    /**
     * Get the description of this port
     */
    public function getDescription(): string
    {
        return (string) $this->ifAlias;
    }

    /**
     * Check if user can access this port.
     *
     * @param  User|int  $user
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
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsDeleted($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), 1],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsNotDeleted($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), 0],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsUp($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
            [$this->qualifyColumn('ifOperStatus'), '=', 'up'],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsDown($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
            [$this->qualifyColumn('ifOperStatus'), '!=', 'up'],
            [$this->qualifyColumn('ifAdminStatus'), '=', 'up'],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsShutdown($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
            [$this->qualifyColumn('ifAdminStatus'), '=', 'down'],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsIgnored($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 1],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 1],
        ]);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeHasErrors($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
        ])->where(function ($query) {
            /** @var Builder $query */
            $query->where($this->qualifyColumn('ifInErrors_delta'), '>', 0)
                ->orWhere($this->qualifyColumn('ifOutErrors_delta'), '>', 0);
        });
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsValid($query)
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
        ]);
    }

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasPortAccess($query, $user);
    }

    public function scopeInPortGroup($query, $portGroup)
    {
        return $query->whereIn($query->qualifyColumn('port_id'), function ($query) use ($portGroup) {
            $query->select('port_id')
                ->from('port_group_port')
                ->where('port_group_id', $portGroup);
        });
    }

    // ---- Define Relationships ----

    public function adsl(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PortAdsl::class, 'port_id');
    }

    public function vdsl(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PortVdsl::class, 'port_id');
    }

    public function events(): MorphMany
    {
        return $this->morphMany(Eventlog::class, 'events', 'type', 'reference');
    }

    public function fdbEntries(): HasMany
    {
        return $this->hasMany(\App\Models\PortsFdb::class, 'port_id', 'port_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\PortGroup::class, 'port_group_port', 'port_id', 'port_group_id');
    }

    public function ipv4(): HasMany
    {
        return $this->hasMany(Ipv4Address::class, 'port_id');
    }

    public function ipv4Networks(): HasManyThrough
    {
        return $this->hasManyThrough(Ipv4Network::class, Ipv4Address::class, 'port_id', 'ipv4_network_id', 'port_id', 'ipv4_network_id');
    }

    public function ipv6(): HasMany
    {
        return $this->hasMany(Ipv6Address::class, 'port_id');
    }

    public function ipv6Networks(): HasManyThrough
    {
        return $this->hasManyThrough(Ipv6Network::class, Ipv6Address::class, 'port_id', 'ipv6_network_id', 'port_id', 'ipv6_network_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(\App\Models\Link::class, 'local_port_id');
    }

    public function remoteLinks(): HasMany
    {
        return $this->hasMany(\App\Models\Link::class, 'remote_port_id');
    }

    public function allLinks(): \Illuminate\Support\Collection
    {
        return $this->links->merge($this->remoteLinks);
    }

    public function xdpLinkedPorts(): BelongsToMany
    {
        return $this->belongsToMany(Port::class, 'links', 'local_port_id', 'remote_port_id');
    }

    public function macLinkedPorts(): BelongsToMany
    {
        return $this->belongsToMany(Port::class, 'view_port_mac_links', 'port_id', 'remote_port_id');
    }

    public function macAccounting(): HasMany
    {
        return $this->hasMany(MacAccounting::class, 'port_id');
    }

    public function macs(): HasMany
    {
        return $this->hasMany(Ipv4Mac::class, 'port_id');
    }

    public function nac(): HasMany
    {
        return $this->hasMany(PortsNac::class, 'port_id');
    }

    public function ospfNeighbors(): HasMany
    {
        return $this->hasMany(OspfNbr::class, 'port_id');
    }

    public function ospfPorts(): HasMany
    {
        return $this->hasMany(OspfPort::class, 'port_id');
    }

    public function pagpParent(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'pagpGroupIfIndex', 'ifIndex');
    }

    public function pseudowires(): HasMany
    {
        return $this->hasMany(Pseudowire::class, 'port_id');
    }

    public function stackChildren(): HasManyThrough
    {
        return $this->hasManyThrough(Port::class, PortStack::class, 'low_port_id', 'port_id', 'port_id', 'high_port_id');
    }

    public function stackParent(): HasManyThrough
    {
        return $this->hasManyThrough(Port::class, PortStack::class, 'high_port_id', 'port_id', 'port_id', 'low_port_id');
    }

    public function statistics(): HasMany
    {
        return $this->hasMany(PortStatistic::class, 'port_id');
    }

    public function stp(): HasMany
    {
        return $this->hasMany(PortStp::class, 'port_id');
    }

    public function transceivers(): HasMany
    {
        return $this->hasMany(Transceiver::class, 'port_id');
    }

    public function users(): BelongsToMany
    {
        // FIXME does not include global read
        return $this->belongsToMany(\App\Models\User::class, 'ports_perms', 'port_id', 'user_id');
    }

    public function vlans(): HasMany
    {
        return $this->hasMany(PortVlan::class, 'port_id');
    }

    public function vrf()
    {
        return $this->hasOne(Vrf::class, 'vrf_id', 'ifVrf');
    }
}
