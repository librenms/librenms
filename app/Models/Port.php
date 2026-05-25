<?php

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LibreNMS\Enum\IfOperStatus;
use LibreNMS\Util\Number;
use LibreNMS\Util\Rewrite;

/**
 * @property IfOperStatus|null $ifOperStatus
 * @property IfOperStatus|null $ifOperStatus_prev
 * @property IfOperStatus|null $ifAdminStatus
 * @property IfOperStatus|null $ifAdminStatus_prev
 */
class Port extends DeviceRelatedModel
{
    use HasFactory;
    use Filterable;

    public $timestamps = false;
    protected $primaryKey = 'port_id';
    protected $guarded = [];
    protected array $filterable = [
        'device_id',
        'ifName',
        'ifDescr',
        'portName',
        'ifSpeed',
        'ifIndex',
        'ifOperStatus',
        'ifAdminStatus',
        'ifDuplex',
        'ifMtu',
        'ifType',
        'ifAlias',
        'ifPhysAddress',
        'ifLastChange',
        'ifVlan',
        'ifTrunk',
        'ifVrf',
        'ignore',
        'disabled',
        'deleted',
        'state',
        'search',
        'errors',
        'groups.id',
        'device.groups.id',
        'device.location_id',
        'device.hostname',
    ];

    protected function casts(): array
    {
        return [
            'ifOperStatus' => IfOperStatus::class,
            'ifOperStatus_prev' => IfOperStatus::class,
            'ifAdminStatus' => IfOperStatus::class,
            'ifAdminStatus_prev' => IfOperStatus::class,
        ];
    }

    /**
     * Initialize this class
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function (Port $port): void {
            // delete related data
            $port->adsl()->delete();
            $port->vdsl()->delete();
            $port->fdbEntries()->delete();
            $port->ipv4()->delete();
            $port->ipv6()->delete();
            $port->macAccounting()->delete();
            $port->macs()->delete();
            $port->nac()->delete();
            $port->nd()->delete();
            $port->ospfNeighbors()->delete();
            $port->ospfPorts()->delete();
            $port->ospfv3Neighbors()->delete();
            $port->ospfv3Ports()->delete();
            $port->pseudowires()->delete();
            $port->statistics()->delete();
            $port->stp()->delete();
            $port->vlans()->delete();
            $port->links()->delete();
            $port->remoteLinks()->delete();
            $port->bills()->detach();

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

        if (\App\Facades\LibrenmsConfig::getOsSetting($os, 'ifname')) {
            $label = $this->ifName;
        } elseif (\App\Facades\LibrenmsConfig::getOsSetting($os, 'ifalias')) {
            $label = $this->ifAlias;
        }

        if (empty($label)) {
            $label = $this->ifDescr;

            if (\App\Facades\LibrenmsConfig::getOsSetting($os, 'ifindex')) {
                $label .= " $this->ifIndex";
            }
        }

        foreach ((array) \App\Facades\LibrenmsConfig::get('rewrite_if', []) as $src => $val) {
            if (Str::contains(strtolower($label), strtolower((string) $src))) {
                $label = $val;
            }
        }

        foreach ((array) \App\Facades\LibrenmsConfig::get('rewrite_if_regexp', []) as $reg => $val) {
            $label = preg_replace($reg . 'i', (string) $val, $label);
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
     * Get port speeds, respecting parsed interface circuit speeds as bps
     *
     * @return array{int, int} [egress bps, ingress bps]
     */
    public function getSpeeds(): array
    {
        $egress = $ingress = (int) $this->ifSpeed;

        if (! empty($this->port_descr_speed)) {
            $speed_parts = explode('/', (string) $this->port_descr_speed, 2);
            $parsed_egress = Number::toBytes($speed_parts[0]);
            $parsed_ingress = isset($speed_parts[1]) ? Number::toBytes($speed_parts[1]) : $parsed_egress;

            if ($parsed_egress > 0 && $parsed_ingress > 0) {
                $egress = $parsed_egress;
                $ingress = $parsed_ingress;
            }
        }

        return [$egress, $ingress];
    }

    // ---- Accessors/Mutators ----

    public function getIfPhysAddressAttribute($mac)
    {
        if (! empty($mac)) {
            return preg_replace('/(..)(..)(..)(..)(..)(..)/', '\\1:\\2:\\3:\\4:\\5:\\6', (string) $mac);
        }

        return null;
    }

    // ---- Query scopes ----

    /**
     * Scope a query to only include deleted ports.
     */
    public function scopeIsDeleted(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), 1],
        ]);
    }

    /**
     * Scope a query to only include non-deleted ports.
     */
    public function scopeIsNotDeleted(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), 0],
        ]);
    }

    /**
     * Scope a query to only include ports that are up.
     */
    public function scopeIsUp(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
            [$this->qualifyColumn('ifOperStatus'), '=', IfOperStatus::Up],
        ]);
    }

    /**
     * Scope a query to only include ports that are down.
     */
    public function scopeIsDown(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
            [$this->qualifyColumn('ifOperStatus'), '!=', IfOperStatus::Up],
            [$this->qualifyColumn('ifAdminStatus'), '=', IfOperStatus::Up],
        ]);
    }

    /**
     * Scope a query to only include ports that are shutdown.
     */
    public function scopeIsShutdown(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
            [$this->qualifyColumn('ifAdminStatus'), '=', IfOperStatus::Down],
        ]);
    }

    /**
     * Scope a query to only include ports that are ignored.
     */
    public function scopeIsIgnored(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 1],
        ]);
    }

    /**
     * Scope a query to only include ports that are disabled.
     */
    public function scopeIsDisabled(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 1],
        ]);
    }

    /**
     * Scope a query to only include ports that have errors.
     */
    public function scopeHasErrors(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('ignore'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
        ])->where(function ($query): void {
            /** @var Builder $query */
            $query->where($this->qualifyColumn('ifInErrors_delta'), '>', 0)
                ->orWhere($this->qualifyColumn('ifOutErrors_delta'), '>', 0);
        });
    }

    /**
     * Scope a query to only include ports that are valid (not deleted or disabled).
     */
    public function scopeIsValid(Builder $query): Builder
    {
        return $query->where([
            [$this->qualifyColumn('deleted'), '=', 0],
            [$this->qualifyColumn('disabled'), '=', 0],
        ]);
    }

    /**
     * Scope a query to only include ports that the given user has access to.
     */
    public function scopeHasAccess(Builder $query, User $user): Builder
    {
        return $this->hasPortAccess($query, $user);
    }

    /**
     * Scope a query to only include ports that are in the given port group.
     */
    public function scopeInPortGroup(Builder $query, PortGroup $portGroup): Builder
    {
        return $query->whereIn($query->qualifyColumn('port_id'), function ($query) use ($portGroup): void {
            $query->select('port_id')
                ->from('port_group_port')
                ->where('port_group_id', $portGroup);
        });
    }

    public function filterErrors(Builder $query, mixed $value, array $config): void
    {
        $query->where(function (Builder $query) use ($value): void {
            $operator = $value ? '>' : '=';
            $boolean = $value ? 'or' : 'and';

            $query->where($this->qualifyColumn('ifInErrors_delta'), $operator, 0, $boolean)
                ->where($this->qualifyColumn('ifOutErrors_delta'), $operator, 0, $boolean);
        });
    }

    /**
     * Handle the "State" filter.
     * up: Admin Up + Oper Up
     * down: Admin Up + Oper NOT Up
     * shutdown: Admin NOT Up
     */
    public function filterState(Builder $query, mixed $value, array $config): void
    {
        $this->applyMappedFilter($query, $value, $config, fn (Builder $q, $state) => match ($state) {
            'shutdown' => $q->where('ifAdminStatus', '!=', 'up'),
            'up' => $q->where('ifAdminStatus', 'up')->where('ifOperStatus', 'up'),
            default => $q->where('ifAdminStatus', 'up')->where('ifOperStatus', '!=', 'up'),
        });
    }

    /**
     * Handle a global text search across multiple port fields
     */
    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['ifName', 'ifAlias', 'ifDescr'], $query, $value, $config);
    }

    /**
     * Custom filter for ifDuplex to handle "unknown" as both 'unknown' and NULL.
     */
    public function filterIfDuplex(Builder $query, mixed $value, array $config): void
    {
        if ($value === 'unknown') {
            $query->where(function ($q): void {
                $q->whereIn('ports.ifDuplex', ['unknown', ''])
                    ->orWhereNull('ports.ifDuplex');
            });

            return;
        }

        $this->applyQueryLogic($query, 'ports.ifDuplex', $value, $config);
    }

    // ---- Define Relationships ----
    /**
     * @return HasOne<PortAdsl, $this>
     */
    public function adsl(): HasOne
    {
        return $this->hasOne(PortAdsl::class, 'port_id');
    }

    /**
     * @return BelongsToMany<Bill, $this>
     */
    public function bills(): BelongsToMany
    {
        return $this->belongsToMany(Bill::class, 'bill_ports', 'port_id', 'bill_id');
    }

    /**
     * @return HasOne<PortVdsl, $this>
     */
    public function vdsl(): HasOne
    {
        return $this->hasOne(PortVdsl::class, 'port_id');
    }

    /**
     * @return MorphMany<Eventlog, $this>
     */
    public function events(): MorphMany
    {
        return $this->morphMany(Eventlog::class, 'events', 'type', 'reference');
    }

    /**
     * @return HasMany<PortsFdb, $this>
     */
    public function fdbEntries(): HasMany
    {
        return $this->hasMany(PortsFdb::class, 'port_id', 'port_id');
    }

    /**
     * @return BelongsToMany<PortGroup, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(PortGroup::class, 'port_group_port', 'port_id', 'port_group_id');
    }

    /**
     * @return HasMany<Ipv4Address, $this>
     */
    public function ipv4(): HasMany
    {
        return $this->hasMany(Ipv4Address::class, 'port_id');
    }

    /**
     * @return HasManyThrough<Ipv4Network, Ipv4Address, $this>
     */
    public function ipv4Networks(): HasManyThrough
    {
        return $this->hasManyThrough(Ipv4Network::class, Ipv4Address::class, 'port_id', 'ipv4_network_id', 'port_id', 'ipv4_network_id');
    }

    /**
     * @return HasMany<Ipv6Address, $this>
     */
    public function ipv6(): HasMany
    {
        return $this->hasMany(Ipv6Address::class, 'port_id');
    }

    /**
     * @return HasManyThrough<Ipv6Network, Ipv6Address, $this>
     */
    public function ipv6Networks(): HasManyThrough
    {
        return $this->hasManyThrough(Ipv6Network::class, Ipv6Address::class, 'port_id', 'ipv6_network_id', 'port_id', 'ipv6_network_id');
    }

    /**
     * @return HasMany<Link, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'local_port_id');
    }

    /**
     * @return HasMany<Link, $this>
     */
    public function remoteLinks(): HasMany
    {
        return $this->hasMany(Link::class, 'remote_port_id');
    }

    /**
     * @return \Illuminate\Support\Collection<int, Link>
     */
    public function allLinks(): \Illuminate\Support\Collection
    {
        return $this->links->merge($this->remoteLinks);
    }

    /**
     * @return BelongsToMany<Port, $this>
     */
    public function xdpLinkedPorts(): BelongsToMany
    {
        return $this->belongsToMany(Port::class, 'links', 'local_port_id', 'remote_port_id');
    }

    /**
     * @return HasManyThrough<Port, Ipv4Mac, $this>
     */
    public function macLinkedPorts(): HasManyThrough
    {
        return $this->hasManyThrough(Port::class, Ipv4Mac::class, 'port_id', 'ifPhysAddress', 'port_id', 'mac_address')
            ->join('ipv4_addresses', function ($j): void {
                $j->on('ipv4_mac.ipv4_address', 'ipv4_addresses.ipv4_address');
                $j->on('ports.port_id', 'ipv4_addresses.port_id');
            })
            ->whereNotIn('mac_address', ['000000000000', 'ffffffffffff']);
    }

    /**
     * @return HasMany<MacAccounting, $this>
     */
    public function macAccounting(): HasMany
    {
        return $this->hasMany(MacAccounting::class, 'port_id');
    }

    /**
     * @return HasMany<Ipv4Mac, $this>
     */
    public function macs(): HasMany
    {
        return $this->hasMany(Ipv4Mac::class, 'port_id');
    }

    /**
     * @return HasMany<PortsNac, $this>
     */
    public function nac(): HasMany
    {
        return $this->hasMany(PortsNac::class, 'port_id');
    }

    /**
     * @return HasMany<Ipv6Nd, $this>
     */
    public function nd(): HasMany
    {
        return $this->hasMany(Ipv6Nd::class, 'port_id');
    }

    /**
     * @return HasMany<OspfNbr, $this>
     */
    public function ospfNeighbors(): HasMany
    {
        return $this->hasMany(OspfNbr::class, 'port_id');
    }

    /**
     * @return HasMany<OspfPort, $this>
     */
    public function ospfPorts(): HasMany
    {
        return $this->hasMany(OspfPort::class, 'port_id');
    }

    /**
     * @return HasMany<Ospfv3Nbr, $this>
     */
    public function ospfv3Neighbors(): HasMany
    {
        return $this->hasMany(Ospfv3Nbr::class, 'port_id');
    }

    /**
     * @return HasMany<Ospfv3Port, $this>
     */
    public function ospfv3Ports(): HasMany
    {
        return $this->hasMany(Ospfv3Port::class, 'port_id');
    }

    /**
     * @return BelongsTo<Port, $this>
     */
    public function pagpParent(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'pagpGroupIfIndex', 'ifIndex');
    }

    /**
     * @return HasMany<Pseudowire, $this>
     */
    public function pseudowires(): HasMany
    {
        return $this->hasMany(Pseudowire::class, 'port_id');
    }

    /**
     * @return HasMany<Qos, $this>
     */
    public function qos(): HasMany
    {
        return $this->hasMany(Qos::class, 'port_id');
    }

    /**
     * @return HasMany<Route, $this>
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'port_id');
    }

    /**
     * @return HasManyThrough<Port, PortStack, $this>
     */
    public function stackChildren(): HasManyThrough
    {
        return $this->hasManyThrough(Port::class, PortStack::class, 'low_port_id', 'port_id', 'port_id', 'high_port_id');
    }

    /**
     * @return HasManyThrough<Port, PortStack, $this>
     */
    public function stackParent(): HasManyThrough
    {
        return $this->hasManyThrough(Port::class, PortStack::class, 'high_port_id', 'port_id', 'port_id', 'low_port_id');
    }

    /**
     * @return HasMany<PortStatistic, $this>
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(PortStatistic::class, 'port_id');
    }

    /**
     * @return HasMany<PortStp, $this>
     */
    public function stp(): HasMany
    {
        return $this->hasMany(PortStp::class, 'port_id');
    }

    /**
     * @return HasMany<Transceiver, $this>
     */
    public function transceivers(): HasMany
    {
        return $this->hasMany(Transceiver::class, 'port_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        // FIXME does not include global read
        return $this->belongsToMany(User::class, 'ports_perms', 'port_id', 'user_id');
    }

    /**
     * @return HasMany<PortVlan, $this>
     */
    public function vlans(): HasMany
    {
        return $this->hasMany(PortVlan::class, 'port_id');
    }

    /**
     * @return HasOne<Vrf, $this>
     */
    public function vrf(): HasOne
    {
        return $this->hasOne(Vrf::class, 'vrf_id', 'ifVrf');
    }

    /**
     * @return HasOne<PortSecurity, $this>
     */
    public function portSecurity(): HasOne
    {
        return $this->hasOne(PortSecurity::class, 'port_id');
    }
}
