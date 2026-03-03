<?php

namespace App\Models;

use App\View\SimpleTemplate;
use Carbon\Carbon;
use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Enum\DeviceStatus;
use LibreNMS\Enum\MaintenanceStatus;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IP;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\IPv6;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;
use Permissions;

/**
 * @property-read int|null $ports_count
 * @property-read int|null $sensors_count
 * @property-read int|null $wirelessSensors_count
 *
 * @method static \Database\Factories\DeviceFactory factory(...$parameters)
 */
class Device extends BaseModel
{
    use PivotEventTrait, HasFactory;

    private ?MaintenanceStatus $maintenanceStatus = null;

    public $timestamps = false;
    protected $primaryKey = 'device_id';
    protected $fillable = [
        'authalgo',
        'authlevel',
        'authname',
        'authpass',
        'community',
        'cryptoalgo',
        'cryptopass',
        'disable_notify',
        'disabled',
        'features',
        'hardware',
        'hostname',
        'display',
        'icon',
        'ignore',
        'ignore_status',
        'ip',
        'location_id',
        'notes',
        'os',
        'override_sysLocation',
        'overwrite_ip',
        'poller_group',
        'port',
        'port_association_mode',
        'purpose',
        'retries',
        'serial',
        'snmp_disable',
        'snmp_max_repeaters',
        'snmpver',
        'status',
        'status_reason',
        'sysDescr',
        'sysName',
        'sysObjectID',
        'timeout',
        'transport',
        'type',
        'version',
        'uptime',
    ];

    /**
     * @return array{inserted: 'datetime', last_discovered: 'datetime', last_polled: 'datetime', last_ping: 'datetime', status: 'boolean'}
     */
    protected function casts(): array
    {
        return [
            'inserted' => 'datetime',
            'last_discovered' => 'datetime',
            'last_polled' => 'datetime',
            'last_ping' => 'datetime',
            'status' => 'boolean',
            'mtu_status' => 'boolean',
            'ignore' => 'boolean',
            'ignore_status' => 'boolean',
            'disabled' => 'boolean',
            'snmp_disable' => 'boolean',
            'disable_notify' => 'boolean',
            'override_sysLocation' => 'boolean',
        ];
    }

    // ---- Helper Functions ----

    public static function findByHostname(string $hostname): ?Device
    {
        return static::where('hostname', $hostname)->first();
    }

    public function pollerTarget(): string
    {
        return ($this->overwrite_ip ?: $this->hostname) ?: '';
    }

    public function ipFamily(): AddressFamily
    {
        return str_ends_with($this->transport ?? '', '6') ? AddressFamily::IPv6 : AddressFamily::IPv4;
    }

    public static function findByIp(?string $ip): ?Device
    {
        if (! IP::isValid($ip)) {
            return null;
        }

        $device = static::where('hostname', $ip)->orWhere('ip', inet_pton($ip))->first();

        if ($device) {
            return $device;
        }

        try {
            $ipv4 = new IPv4($ip);
            $port = Ipv4Address::where('ipv4_address', (string) $ipv4)
                ->with('port', 'port.device')
                ->firstOrFail()->port;
            if ($port) {
                return $port->device;
            }
        } catch (InvalidIpException|ModelNotFoundException) {
            //
        }

        try {
            $ipv6 = new IPv6($ip);
            $port = Ipv6Address::where('ipv6_address', $ipv6->uncompressed())
                ->with(['port', 'port.device'])
                ->firstOrFail()->port;
            if ($port) {
                return $port->device;
            }
        } catch (InvalidIpException|ModelNotFoundException) {
            //
        }

        return null;
    }

    public function hasSnmpInfo(): bool
    {
        if ($this->snmpver == 'v3') {
            if ($this->authlevel == 'authNoPriv') {
                return ! empty($this->authname) && ! empty($this->authpass);
            }

            if ($this->authlevel == 'authPriv') {
                return ! empty($this->authname)
                    && ! empty($this->authpass)
                    && ! empty($this->cryptoalgo)
                    && ! empty($this->cryptopass);
            }

            return $this->authlevel !== 'noAuthNoPriv'; // reject if not noAuthNoPriv
        }

        if ($this->snmpver == 'v2c' || $this->snmpver == 'v1') {
            return ! empty($this->community);
        }

        return false; // no known snmpver
    }

    /**
     * Get VRF contexts to poll.
     * If no contexts are found, return the default context ''
     *
     * @return array
     */
    public function getVrfContexts(): array
    {
        return $this->vrfLites->isEmpty() ? [''] : $this->vrfLites->pluck('context_name')->all();
    }

    /**
     * Get the display name of this device based on the display format string
     * The default is {{ $hostname }} controlled by the device_display_default setting
     */
    public function displayName(): string
    {
        $hostname_is_ip = IP::isValid($this->hostname);

        return SimpleTemplate::parse($this->display ?: \App\Facades\LibrenmsConfig::get('device_display_default', '{{ $hostname }}'), [
            'hostname' => $this->hostname,
            'sysName' => $this->sysName ?: $this->hostname,
            'sysName_fallback' => $hostname_is_ip ? $this->sysName : $this->hostname,
            'ip' => $this->overwrite_ip ?: ($hostname_is_ip ? $this->hostname : $this->ip),
        ]);
    }

    /**
     * Returns the device name if not already displayed
     */
    public function name(): string
    {
        $display = $this->displayName();

        if (! Str::contains($display, $this->hostname)) {
            return (string) $this->hostname;
        } elseif (! Str::contains($display, $this->sysName)) {
            return (string) $this->sysName;
        }

        return '';
    }

    public function isUnderMaintenance(): bool
    {
        return $this->getMaintenanceStatus() !== MaintenanceStatus::None;
    }

    public function getMaintenanceStatus(): MaintenanceStatus
    {
        if (! $this->device_id) {
            return MaintenanceStatus::None;
        }

        // use cached status
        if ($this->maintenanceStatus !== null) {
            return $this->maintenanceStatus;
        }

        $behavior = AlertSchedule::isActive()
            ->where(function (Builder $query): void {
                $query->whereHas('devices', function (Builder $query): void {
                    $query->where('alert_schedulables.alert_schedulable_id', $this->device_id);
                });

                if ($this->groups->isNotEmpty()) {
                    $query->orWhereHas('deviceGroups', function (Builder $query): void {
                        $query->whereIntegerInRaw('alert_schedulables.alert_schedulable_id', $this->groups->pluck('id'));
                    });
                }

                if ($this->location) {
                    $query->orWhereHas('locations', function (Builder $query): void {
                        $query->where('alert_schedulables.alert_schedulable_id', $this->location->id);
                    });
                }
            })
            ->value('behavior');

        $this->maintenanceStatus = MaintenanceStatus::fromBehavior($behavior);

        return $this->maintenanceStatus;
    }

    public function getDeviceStatus(): DeviceStatus
    {
        if ($this->disabled) {
            return DeviceStatus::Disabled;
        }

        if ($this->ignore) {
            return $this->status ? DeviceStatus::IgnoredUp : DeviceStatus::IgnoredDown;
        }

        if ($this->status) {
            return DeviceStatus::Up;
        }

        return $this->last_polled ? DeviceStatus::Down : DeviceStatus::NeverPolled;
    }

    /**
     * Get the shortened display name of this device.
     * Length is always overridden by shorthost_target_length.
     *
     * @param  int  $length  length to shorten to, will not break up words so may be longer
     * @return string
     */
    public function shortDisplayName($length = 12)
    {
        $name = $this->displayName();

        // IP addresses should not be shortened
        if (IP::isValid($name)) {
            return $name;
        }

        $length = \App\Facades\LibrenmsConfig::get('shorthost_target_length', $length);
        if ($length < strlen($name)) {
            $take = max(substr_count($name, '.', 0, $length), 1);

            return implode('.', array_slice(explode('.', $name), 0, $take));
        }

        return $name;
    }

    /**
     * Get the current DeviceOutage if there is one (if device is down)
     */
    public function getCurrentOutage(): ?DeviceOutage
    {
        if ($this->relationLoaded('outages')) {
            return $this->outages->whereNull('up_again')->sortBy('going_down', descending: true)->first();
        }

        return $this->outages()->whereNull('up_again')->orderBy('going_down', 'desc')->first();
    }

    /**
     * Get the time this device went down
     */
    public function downSince(): Carbon
    {
        $deviceOutage = $this->getCurrentOutage();

        if ($deviceOutage) {
            return Carbon::createFromTimestamp((int) $deviceOutage->going_down);
        }

        return $this->last_polled ?? Carbon::now();
    }

    /**
     * Check if user can access this device.
     *
     * @param  User  $user
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

        return Permissions::canAccessDevice($this->device_id, $user->user_id);
    }

    public function formatDownUptime($short = false): string
    {
        $time = ($this->status == 1) ? $this->uptime : (int) $this->last_polled?->diffInSeconds(null, true);

        return Time::formatInterval($time, $short);
    }

    /**
     * @return string
     */
    public function logo()
    {
        $base_name = pathinfo($this->icon, PATHINFO_FILENAME);
        $options = [
            "images/logos/$base_name.svg",
            "images/logos/$base_name.png",
            "images/os/$base_name.svg",
            "images/os/$base_name.png",
        ];

        foreach ($options as $file) {
            if (is_file(public_path() . "/$file")) {
                return asset($file);
            }
        }

        return asset('images/os/generic.svg');
    }

    /**
     * Update the max_depth field based on parents
     * Performs SQL query, so make sure all parents are saved first
     *
     * @param  int  $exclude  exclude a device_id from being considered (used for deleting)
     */
    public function updateMaxDepth($exclude = null)
    {
        // optimize for memory instead of time
        $query = $this->parents()->getQuery();
        if (! is_null($exclude)) {
            $query->where('device_id', '!=', $exclude);
        }

        $count = $query->count();
        if ($count === 0) {
            if ($this->children()->count() === 0) {
                $this->max_depth = 0; // no children or parents
            } else {
                $this->max_depth = 1; // has children
            }
        } else {
            $parents_max_depth = $query->max('max_depth');
            $this->max_depth = $parents_max_depth + 1;
        }

        $this->save();
    }

    /**
     * Device dependency check to see if this node is standalone or not.
     * Standalone is a special case where the device has no parents or children and is denoted by a max_depth of 0
     *
     * Only checks on root nodes (where max_depth is 1 or 0)
     */
    public function validateStandalone()
    {
        if ($this->max_depth === 0 && $this->children()->count() > 0) {
            $this->max_depth = 1; // has children
        } elseif ($this->max_depth === 1 && $this->parents()->count() === 0) {
            $this->max_depth = 0; // no children or parents
        }

        $this->save();
    }

    public function getAttrib($name, $default = null)
    {
        return $this->attribs->pluck('attrib_value', 'attrib_type')->get($name, $default);
    }

    public function setAttrib($name, $value)
    {
        $attrib = $this->attribs->first(fn ($item) => $item->attrib_type === $name);

        if (! $attrib) {
            $attrib = new DeviceAttrib(['attrib_type' => $name]);
            $this->attribs->push($attrib);
        }

        $attrib->attrib_value = $value;

        return (bool) $this->attribs()->save($attrib);
    }

    public function forgetAttrib($name)
    {
        $attrib_index = $this->attribs->search(fn ($attrib) => $attrib->attrib_type === $name);

        if ($attrib_index !== false) {
            $deleted = (bool) $this->attribs->get($attrib_index)->delete();
            // only forget the attrib_index after delete, otherwise delete() will fail fatally with:
            // Symfony\\Component\\Debug\Exception\\FatalThrowableError(code: 0):  Call to a member function delete() on null
            $this->attribs->forget((string) $attrib_index);

            return $deleted;
        }

        return false;
    }

    public function getAttribs()
    {
        return $this->attribs->pluck('attrib_value', 'attrib_type')->toArray();
    }

    /**
     * Update the location to the correct location and update GPS if needed
     *
     * @param  Location|string|null  $new_location  location data
     * @param  bool  $doLookup  try to lookup the GPS coordinates
     * @param  bool  $user_override  Ignore user override and update the location anyway
     */
    public function setLocation(Location|string|null $new_location, bool $doLookup = false, bool $user_override = false): void
    {
        $new_location = $new_location instanceof Location ? $new_location : new Location(['location' => $new_location]);
        $new_location->location = $new_location->location ? Rewrite::location($new_location->location) : null;
        $coord = array_filter($new_location->only(['lat', 'lng']));

        if ($user_override || ! $this->override_sysLocation) {
            if (! $new_location->location) { // disassociate if the location name is empty
                $this->location()->dissociate();

                return;
            }

            if (! $this->relationLoaded('location') || $this->location?->location !== $new_location->location) {
                if (! $new_location->exists) { // don't fetch if new location persisted to the DB, just use it
                    $new_location = Location::firstOrCreate(['location' => $new_location->location], $coord);
                }
                $this->location()->associate($new_location);
            }
        }

        // set coordinates
        if ($this->location && ! $this->location->fixed_coordinates) {
            $this->location->fill($coord);
            if ($doLookup && empty($coord)) { // only if requested and coordinates not passed explicitly
                $this->location->lookupCoordinates($this->hostname);
            }
        }
    }

    // ---- Accessors/Mutators ----

    public function getIconAttribute($icon): string
    {
        return Str::start(Url::findOsImage($this->os, $this->features, $icon), 'images/os/');
    }

    public function getIpAttribute($ip): ?string
    {
        if (empty($ip)) {
            return null;
        }

        // @ suppresses warning, inet_ntop() returns false if it fails
        return @inet_ntop($ip) ?: null;
    }

    public function setIpAttribute($ip): void
    {
        $this->attributes['ip'] = $ip ? inet_pton($ip) : null;
    }

    public function setStatusAttribute($status): void
    {
        $this->attributes['status'] = (int) $status;
    }

    public function setSysDescrAttribute(?string $sysDescr): void
    {
        $this->attributes['sysDescr'] = $sysDescr === null ? null : trim(str_replace(chr(218), "\n", $sysDescr), "\\\" \r\n\t\0");
    }

    public function setSysNameAttribute(?string $sysName): void
    {
        $this->attributes['sysName'] = $sysName === null ? null : str_replace("\n", '', strtolower(trim($sysName)));
    }

    // ---- Query scopes ----

    public function scopeIsUp($query)
    {
        return $query->where([
            ['status', '=', 1],
            ['ignore', '=', 0],
            ['disable_notify', '=', 0],
            ['disabled', '=', 0],
        ]);
    }

    public function scopeIsActive($query)
    {
        return $query->where([
            ['ignore', '=', 0],
            ['disabled', '=', 0],
        ]);
    }

    public function scopeIsDown($query)
    {
        return $query->where([
            ['status', '=', 0],
            ['ignore', '=', 0],
            ['disabled', '=', 0],
        ]);
    }

    public function scopeIsIgnored($query)
    {
        return $query->where([
            ['ignore', '=', 1],
            ['disabled', '=', 0],
        ]);
    }

    public function scopeNotIgnored($query)
    {
        return $query->where([
            ['ignore', '=', 0],
        ]);
    }

    public function scopeIsDisabled($query)
    {
        return $query->where([
            ['disabled', '=', 1],
        ]);
    }

    public function scopeIsDisableNotify($query)
    {
        return $query->where([
            ['disable_notify', '=', 1],
        ]);
    }

    public function scopeIsNotDisabled($query)
    {
        return $query->where([
            ['disable_notify', '=', 0],
            ['disabled', '=', 0],
        ]);
    }

    public function scopeWhereAttributeDisabled(Builder $query, string $attribute): Builder
    {
        return $query->leftJoin('devices_attribs', function (JoinClause $query) use ($attribute): void {
            $query->on('devices.device_id', 'devices_attribs.device_id')
                ->where('devices_attribs.attrib_type', $attribute);
        })->where(function (Builder $query): void {
            $query->whereNull('devices_attribs.attrib_value')
                ->orWhere('devices_attribs.attrib_value', '!=', 'true');
        });
    }

    public function scopeWhereUptime($query, $uptime, $modifier = '<')
    {
        return $query->where([
            ['uptime', '>', 0],
            ['uptime', $modifier, $uptime],
        ]);
    }

    public function scopeCanPing(Builder $query): Builder
    {
        return $this->scopeWhereAttributeDisabled($query->where('disabled', 0), 'override_icmp_disable');
    }

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    public function scopeInDeviceGroup($query, $deviceGroup)
    {
        return $query->whereIn(
            $query->qualifyColumn('device_id'), function ($query) use ($deviceGroup): void {
                $query->select('device_id')
                ->from('device_group_device')
                ->whereIn('device_group_id', Arr::wrap($deviceGroup));
            }
        );
    }

    public function scopeNotInDeviceGroup($query, $deviceGroup)
    {
        return $query->whereNotIn(
            $query->qualifyColumn('device_id'), function ($query) use ($deviceGroup): void {
                $query->select('device_id')
                ->from('device_group_device')
                ->whereIn('device_group_id', Arr::wrap($deviceGroup));
            }
        );
    }

    public function scopeInServiceTemplate($query, $serviceTemplate)
    {
        return $query->whereIn(
            $query->qualifyColumn('device_id'), function ($query) use ($serviceTemplate): void {
                $query->select('device_id')
                ->from('service_templates_device')
                ->where('service_template_id', $serviceTemplate);
            }
        );
    }

    public function scopeNotInServiceTemplate($query, $serviceTemplate)
    {
        return $query->whereNotIn(
            $query->qualifyColumn('device_id'), function ($query) use ($serviceTemplate): void {
                $query->select('device_id')
                ->from('service_templates_device')
                ->where('service_template_id', $serviceTemplate);
            }
        );
    }

    public function scopeWhereDeviceSpec(Builder $query, ?string $deviceSpec): Builder
    {
        if (empty($deviceSpec)) {
            return $query;
        } elseif ($deviceSpec == 'all') {
            return $query;
        } elseif ($deviceSpec == 'new') {
            return $query->whereNull('last_discovered');
        } elseif ($deviceSpec == 'even') {
            return $query->whereRaw('device_id % 2 = 0');
        } elseif ($deviceSpec == 'odd') {
            return $query->whereRaw('device_id % 2 = 1');
        } elseif (is_numeric($deviceSpec)) {
            return $query->where('device_id', $deviceSpec);
        } elseif (str_contains($deviceSpec, '*')) {
            return $query->where('hostname', 'like', str_replace('*', '%', $deviceSpec));
        }

        return $query->where('hostname', $deviceSpec);
    }

    // ---- Define Relationships ----
    /**
     * @return HasMany<AccessPoint, $this>
     */
    public function accessPoints(): HasMany
    {
        return $this->hasMany(AccessPoint::class, 'device_id');
    }

    /**
     * @return HasMany<Alert, $this>
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'device_id');
    }

    /**
     * @return HasMany<AlertLog, $this>
     */
    public function alertLogs(): HasMany
    {
        return $this->hasMany(AlertLog::class, 'device_id');
    }

    /**
     * @return MorphToMany<AlertSchedule, $this>
     */
    public function alertSchedules(): MorphToMany
    {
        return $this->morphToMany(AlertSchedule::class, 'alert_schedulable', 'alert_schedulables', 'alert_schedulable_id', 'schedule_id');
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'device_id');
    }

    /**
     * @return HasMany<DeviceAttrib, $this>
     */
    public function attribs(): HasMany
    {
        return $this->hasMany(DeviceAttrib::class, 'device_id');
    }

    /**
     * @return HasMany<Availability, $this>
     */
    public function availability(): HasMany
    {
        return $this->hasMany(Availability::class, 'device_id');
    }

    /**
     * @return HasMany<BgpPeer, $this>
     */
    public function bgppeers(): HasMany
    {
        return $this->hasMany(BgpPeer::class, 'device_id');
    }

    /**
     * @return HasMany<CefSwitching, $this>
     */
    public function cefSwitching(): HasMany
    {
        return $this->hasMany(CefSwitching::class, 'device_id');
    }

    /**
     * @return BelongsToMany<Device, $this>
     */
    public function children(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'device_relationships', 'parent_device_id', 'child_device_id');
    }

    /**
     * @return HasMany<Component, $this>
     */
    public function components(): HasMany
    {
        return $this->hasMany(Component::class, 'device_id');
    }

    /**
     * @return HasMany<DiskIo, $this>
     */
    public function diskIo(): HasMany
    {
        return $this->hasMany(DiskIo::class, 'device_id');
    }

    /**
     * @return HasMany<HrDevice, $this>
     */
    public function hostResources(): HasMany
    {
        return $this->hasMany(HrDevice::class, 'device_id');
    }

    /**
     * @return HasOne<HrSystem, $this>
     */
    public function hostResourceValues(): HasOne
    {
        return $this->hasOne(HrSystem::class, 'device_id');
    }

    /**
     * @return HasMany<EntPhysical, $this>
     */
    public function entityPhysical(): HasMany
    {
        return $this->hasMany(EntPhysical::class, 'device_id');
    }

    /**
     * @return HasMany<EntityState, $this>
     */
    public function entityState(): HasMany
    {
        return $this->hasMany(EntityState::class, 'device_id');
    }

    /**
     * @return HasMany<Eventlog, $this>
     */
    public function eventlogs(): HasMany
    {
        return $this->hasMany(Eventlog::class, 'device_id', 'device_id');
    }

    /**
     * @return HasMany<DeviceGraph, $this>
     */
    public function graphs(): HasMany
    {
        return $this->hasMany(DeviceGraph::class, 'device_id');
    }

    /**
     * @return BelongsToMany<DeviceGroup, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(DeviceGroup::class, 'device_group_device', 'device_id', 'device_group_id');
    }

    /**
     * @return HasMany<IpsecTunnel, $this>
     */
    public function ipsecTunnels(): HasMany
    {
        return $this->hasMany(IpsecTunnel::class, 'device_id');
    }

    /**
     * @return HasManyThrough<Ipv4Address, Port, $this>
     */
    public function ipv4(): HasManyThrough
    {
        return $this->hasManyThrough(Ipv4Address::class, Port::class, 'device_id', 'port_id', 'device_id', 'port_id');
    }

    /**
     * @return HasManyThrough<Ipv6Address, Port, $this>
     */
    public function ipv6(): HasManyThrough
    {
        return $this->hasManyThrough(Ipv6Address::class, Port::class, 'device_id', 'port_id', 'device_id', 'port_id');
    }

    /**
     * @return HasMany<IsisAdjacency, $this>
     */
    public function isisAdjacencies(): HasMany
    {
        return $this->hasMany(IsisAdjacency::class, 'device_id', 'device_id');
    }

    /**
     * @return HasMany<Link, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(Link::class, 'local_device_id');
    }

    /**
     * @return HasMany<Link, $this>
     */
    public function remoteLinks(): HasMany
    {
        return $this->hasMany(Link::class, 'remote_device_id');
    }

    public function allLinks(): \Illuminate\Support\Collection
    {
        return $this->links->merge($this->remoteLinks);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    /**
     * @return HasMany<Ipv4Mac, $this>
     */
    public function macs(): HasMany
    {
        return $this->hasMany(Ipv4Mac::class, 'device_id');
    }

    /**
     * @return HasManyThrough<CustomMap, CustomMapNode, $this>
     */
    public function maps(): HasManyThrough
    {
        return $this->hasManyThrough(CustomMap::class, CustomMapNode::class, 'device_id', 'custom_map_id', 'device_id', 'custom_map_id')
            ->distinct();
    }

    /**
     * @return HasMany<MefInfo, $this>
     */
    public function mefInfo(): HasMany
    {
        return $this->hasMany(MefInfo::class, 'device_id');
    }

    /**
     * @return HasMany<MuninPlugin, $this>
     */
    public function muninPlugins(): HasMany
    {
        return $this->hasMany(MuninPlugin::class, 'device_id');
    }

    /**
     * @return HasMany<Ipv6Nd, $this>
     */
    public function nd(): HasMany
    {
        return $this->hasMany(Ipv6Nd::class, 'device_id');
    }

    /**
     * @return HasMany<NetscalerVserver, $this>
     */
    public function netscalerVservers(): HasMany
    {
        return $this->hasMany(NetscalerVserver::class, 'device_id');
    }

    /**
     * @return HasMany<OspfArea, $this>
     */
    public function ospfAreas(): HasMany
    {
        return $this->hasMany(OspfArea::class, 'device_id');
    }

    /**
     * @return HasMany<OspfInstance, $this>
     */
    public function ospfInstances(): HasMany
    {
        return $this->hasMany(OspfInstance::class, 'device_id');
    }

    /**
     * @return HasMany<OspfNbr, $this>
     */
    public function ospfNbrs(): HasMany
    {
        return $this->hasMany(OspfNbr::class, 'device_id');
    }

    /**
     * @return HasMany<OspfPort, $this>
     */
    public function ospfPorts(): HasMany
    {
        return $this->hasMany(OspfPort::class, 'device_id');
    }

    /**
     * @return HasMany<Ospfv3Area, $this>
     */
    public function ospfv3Areas(): HasMany
    {
        return $this->hasMany(Ospfv3Area::class, 'device_id');
    }

    /**
     * @return HasMany<Ospfv3Instance, $this>
     */
    public function ospfv3Instances(): HasMany
    {
        return $this->hasMany(Ospfv3Instance::class, 'device_id');
    }

    /**
     * @return HasMany<Ospfv3Nbr, $this>
     */
    public function ospfv3Nbrs(): HasMany
    {
        return $this->hasMany(Ospfv3Nbr::class, 'device_id');
    }

    /**
     * @return HasMany<Ospfv3Port, $this>
     */
    public function ospfv3Ports(): HasMany
    {
        return $this->hasMany(Ospfv3Port::class, 'device_id');
    }

    /**
     * @return HasMany<Package, $this>
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class, 'device_id', 'device_id');
    }

    /**
     * @return BelongsToMany<Device, $this>
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'device_relationships', 'child_device_id', 'parent_device_id');
    }

    /**
     * @return HasMany<Port, $this>
     */
    public function ports(): HasMany
    {
        return $this->hasMany(Port::class, 'device_id', 'device_id');
    }

    /**
     * @return HasManyThrough<PortAdsl, Port, $this>
     */
    public function portsAdsl(): HasManyThrough
    {
        return $this->hasManyThrough(PortAdsl::class, Port::class, 'device_id', 'port_id');
    }

    /**
     * @return HasMany<PortsFdb, $this>
     */
    public function portsFdb(): HasMany
    {
        return $this->hasMany(PortsFdb::class, 'device_id', 'device_id');
    }

    /**
     * @return HasMany<PortsNac, $this>
     */
    public function portsNac(): HasMany
    {
        return $this->hasMany(PortsNac::class, 'device_id', 'device_id');
    }

    /**
     * @return HasMany<PortStack, $this>
     */
    public function portsStack(): HasMany
    {
        return $this->hasMany(PortStack::class, 'device_id', 'device_id');
    }

    /**
     * @return HasMany<PortStp, $this>
     */
    public function portsStp(): HasMany
    {
        return $this->hasMany(PortStp::class, 'device_id', 'device_id');
    }

    /**
     * @return HasManyThrough<PortSecurity, Port, $this>
     */
    public function portSecurity(): HasManyThrough
    {
        return $this->hasManyThrough(PortSecurity::class, Port::class, 'device_id', 'port_id');
    }

    /**
     * @return HasManyThrough<PortVdsl, Port, $this>
     */
    public function portsVdsl(): HasManyThrough
    {
        return $this->hasManyThrough(PortVdsl::class, Port::class, 'device_id', 'port_id');
    }

    /**
     * @return HasMany<PortVlan, $this>
     */
    public function portsVlan(): HasMany
    {
        return $this->hasMany(PortVlan::class, 'device_id', 'device_id');
    }

    /**
     * @return HasMany<Process, $this>
     */
    public function processes(): HasMany
    {
        return $this->hasMany(Process::class, 'device_id');
    }

    /**
     * @return HasMany<Processor, $this>
     */
    public function processors(): HasMany
    {
        return $this->hasMany(Processor::class, 'device_id');
    }

    /**
     * @return HasMany<Route, $this>
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'device_id');
    }

    /**
     * @return BelongsToMany<AlertRule, $this>
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(AlertRule::class, 'alert_device_map', 'device_id', 'rule_id');
    }

    /**
     * @return HasMany<Sensor, $this>
     */
    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class, 'device_id');
    }

    /**
     * @return BelongsToMany<ServiceTemplate, $this>
     */
    public function serviceTemplates(): BelongsToMany
    {
        return $this->belongsToMany(ServiceTemplate::class, 'service_templates_device', 'device_id', 'service_template_id');
    }

    /**
     * @return HasMany<Service, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'device_id');
    }

    /**
     * @return HasOne<DeviceStats, $this>
     */
    public function stats(): HasOne
    {
        return $this->hasOne(DeviceStats::class, 'device_id');
    }

    /**
     * @return HasMany<Storage, $this>
     */
    public function storage(): HasMany
    {
        return $this->hasMany(Storage::class, 'device_id');
    }

    /**
     * @return HasMany<Stp, $this>
     */
    public function stpInstances(): HasMany
    {
        return $this->hasMany(Stp::class, 'device_id');
    }

    /**
     * @return HasMany<PortStp, $this>
     */
    public function stpPorts(): HasMany
    {
        return $this->hasMany(PortStp::class, 'device_id');
    }

    /**
     * @return HasMany<Mempool, $this>
     */
    public function mempools(): HasMany
    {
        return $this->hasMany(Mempool::class, 'device_id');
    }

    /**
     * @return HasMany<MplsLsp, $this>
     */
    public function mplsLsps(): HasMany
    {
        return $this->hasMany(MplsLsp::class, 'device_id');
    }

    /**
     * @return HasMany<MplsLspPath, $this>
     */
    public function mplsLspPaths(): HasMany
    {
        return $this->hasMany(MplsLspPath::class, 'device_id');
    }

    /**
     * @return HasMany<MplsSdp, $this>
     */
    public function mplsSdps(): HasMany
    {
        return $this->hasMany(MplsSdp::class, 'device_id');
    }

    /**
     * @return HasMany<MplsService, $this>
     */
    public function mplsServices(): HasMany
    {
        return $this->hasMany(MplsService::class, 'device_id');
    }

    /**
     * @return HasMany<MplsSap, $this>
     */
    public function mplsSaps(): HasMany
    {
        return $this->hasMany(MplsSap::class, 'device_id');
    }

    /**
     * @return HasMany<MplsSdpBind, $this>
     */
    public function mplsSdpBinds(): HasMany
    {
        return $this->hasMany(MplsSdpBind::class, 'device_id');
    }

    /**
     * @return HasMany<MplsTunnelArHop, $this>
     */
    public function mplsTunnelArHops(): HasMany
    {
        return $this->hasMany(MplsTunnelArHop::class, 'device_id');
    }

    /**
     * @return HasMany<MplsTunnelCHop, $this>
     */
    public function mplsTunnelCHops(): HasMany
    {
        return $this->hasMany(MplsTunnelCHop::class, 'device_id');
    }

    /**
     * @return HasMany<DeviceOutage, $this>
     */
    public function outages(): HasMany
    {
        return $this->hasMany(DeviceOutage::class, 'device_id');
    }

    /**
     * @return HasMany<PrinterSupply, $this>
     */
    public function printerSupplies(): HasMany
    {
        return $this->hasMany(PrinterSupply::class, 'device_id');
    }

    /**
     * @return HasMany<Pseudowire, $this>
     */
    public function pseudowires(): HasMany
    {
        return $this->hasMany(Pseudowire::class, 'device_id');
    }

    /**
     * @return HasMany<LoadbalancerRserver, $this>
     */
    public function rServers(): HasMany
    {
        return $this->hasMany(LoadbalancerRserver::class, 'device_id');
    }

    /**
     * @return HasMany<Qos, $this>
     */
    public function qos(): HasMany
    {
        return $this->hasMany(Qos::class, 'device_id');
    }

    /**
     * @return HasMany<Sla, $this>
     */
    public function slas(): HasMany
    {
        return $this->hasMany(Sla::class, 'device_id');
    }

    /**
     * @return HasMany<Syslog, $this>
     */
    public function syslogs(): HasMany
    {
        return $this->hasMany(Syslog::class, 'device_id', 'device_id');
    }

    /**
     * @return HasMany<TnmsneInfo, $this>
     */
    public function tnmsNeInfo(): HasMany
    {
        return $this->hasMany(TnmsneInfo::class, 'device_id');
    }

    /**
     * @return HasMany<Transceiver, $this>
     */
    public function transceivers(): HasMany
    {
        return $this->hasMany(Transceiver::class, 'device_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        // FIXME does not include global read
        return $this->belongsToMany(User::class, 'devices_perms', 'device_id', 'user_id');
    }

    /**
     * @return HasMany<Vminfo, $this>
     */
    public function vminfo(): HasMany
    {
        return $this->hasMany(Vminfo::class, 'device_id');
    }

    /**
     * @return HasMany<Vlan, $this>
     */
    public function vlans(): HasMany
    {
        return $this->hasMany(Vlan::class, 'device_id');
    }

    /**
     * @return HasMany<VrfLite, $this>
     */
    public function vrfLites(): HasMany
    {
        return $this->hasMany(VrfLite::class, 'device_id');
    }

    /**
     * @return HasMany<Vrf, $this>
     */
    public function vrfs(): HasMany
    {
        return $this->hasMany(Vrf::class, 'device_id');
    }

    /**
     * @return HasMany<LoadbalancerVserver, $this>
     */
    public function vServers(): HasMany
    {
        return $this->hasMany(LoadbalancerVserver::class, 'device_id');
    }

    /**
     * @return HasMany<WirelessSensor, $this>
     */
    public function wirelessSensors(): HasMany
    {
        return $this->hasMany(WirelessSensor::class, 'device_id');
    }
}
