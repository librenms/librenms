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
        'version',
        'uptime',
    ];

    protected $casts = [
        'inserted' => 'datetime',
        'last_discovered' => 'datetime',
        'last_polled' => 'datetime',
        'last_ping' => 'datetime',
        'status' => 'boolean',
    ];

    // ---- Helper Functions ----

    public static function findByHostname(string $hostname): ?Device
    {
        return static::where('hostname', $hostname)->first();
    }

    /**
     * Returns IP/Hostname where polling will be targeted to
     *
     * @param  string|array  $device  hostname which will be triggered
     *                                array  $device associative array with device data
     * @return string IP/Hostname to which Device polling is targeted
     */
    public static function pollerTarget($device)
    {
        if (! is_array($device)) {
            $ret = static::where('hostname', $device)->first(['hostname', 'overwrite_ip']);
            if (empty($ret)) {
                return $device;
            }
            $overwrite_ip = $ret->overwrite_ip;
            $hostname = $ret->hostname;
        } elseif (array_key_exists('overwrite_ip', $device)) {
            $overwrite_ip = $device['overwrite_ip'];
            $hostname = $device['hostname'];
        } else {
            return $device['hostname'];
        }

        return $overwrite_ip ?: $hostname;
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
        } catch (InvalidIpException $e) {
            //
        } catch (ModelNotFoundException $e) {
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
        } catch (InvalidIpException $e) {
            //
        } catch (ModelNotFoundException $e) {
            //
        }

        return null;
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

        return SimpleTemplate::parse($this->display ?: \LibreNMS\Config::get('device_display_default', '{{ $hostname }}'), [
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

    public function isUnderMaintenance()
    {
        if (! $this->device_id) {
            return false;
        }

        $query = AlertSchedule::isActive()
            ->where(function (Builder $query) {
                $query->whereHas('devices', function (Builder $query) {
                    $query->where('alert_schedulables.alert_schedulable_id', $this->device_id);
                });

                if ($this->groups->isNotEmpty()) {
                    $query->orWhereHas('deviceGroups', function (Builder $query) {
                        $query->whereIntegerInRaw('alert_schedulables.alert_schedulable_id', $this->groups->pluck('id'));
                    });
                }

                if ($this->location) {
                    $query->orWhereHas('locations', function (Builder $query) {
                        $query->where('alert_schedulables.alert_schedulable_id', $this->location->id);
                    });
                }
            });

        return $query->exists();
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

        $length = \LibreNMS\Config::get('shorthost_target_length', $length);
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
        return $this->relationLoaded('outages')
            ? $this->outages->whereNull('up_again')->sortBy('going_down', descending: true)->first()
            : $this->outages()->whereNull('up_again')->orderBy('going_down', 'desc')->first();
    }

    /**
     * Get the time this device went down
     */
    public function downSince(): Carbon
    {
        return Carbon::createFromTimestamp((int) $this->getCurrentOutage()?->going_down);
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
        $time = ($this->status == 1) ? $this->uptime : $this->last_polled?->diffInSeconds();

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

    public function getAttrib($name)
    {
        return $this->attribs->pluck('attrib_value', 'attrib_type')->get($name);
    }

    public function setAttrib($name, $value)
    {
        $attrib = $this->attribs->first(function ($item) use ($name) {
            return $item->attrib_type === $name;
        });

        if (! $attrib) {
            $attrib = new DeviceAttrib(['attrib_type' => $name]);
            $this->attribs->push($attrib);
        }

        $attrib->attrib_value = $value;

        return (bool) $this->attribs()->save($attrib);
    }

    public function forgetAttrib($name)
    {
        $attrib_index = $this->attribs->search(function ($attrib) use ($name) {
            return $attrib->attrib_type === $name;
        });

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
     * @param  \App\Models\Location|string  $new_location  location data
     * @param  bool  $doLookup  try to lookup the GPS coordinates
     */
    public function setLocation($new_location, bool $doLookup = false)
    {
        $new_location = $new_location instanceof Location ? $new_location : new Location(['location' => $new_location]);
        $new_location->location = $new_location->location ? Rewrite::location($new_location->location) : null;
        $coord = array_filter($new_location->only(['lat', 'lng']));

        if (! $this->override_sysLocation) {
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
            ['disable_notify', '=', 0],
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
        return $query->leftJoin('devices_attribs', function (JoinClause $query) use ($attribute) {
            $query->on('devices.device_id', 'devices_attribs.device_id')
                ->where('devices_attribs.attrib_type', $attribute);
        })->where(function (Builder $query) {
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
            $query->qualifyColumn('device_id'), function ($query) use ($deviceGroup) {
                $query->select('device_id')
                ->from('device_group_device')
                ->whereIn('device_group_id', Arr::wrap($deviceGroup));
            }
        );
    }

    public function scopeNotInDeviceGroup($query, $deviceGroup)
    {
        return $query->whereNotIn(
            $query->qualifyColumn('device_id'), function ($query) use ($deviceGroup) {
                $query->select('device_id')
                ->from('device_group_device')
                ->whereIn('device_group_id', Arr::wrap($deviceGroup));
            }
        );
    }

    public function scopeInServiceTemplate($query, $serviceTemplate)
    {
        return $query->whereIn(
            $query->qualifyColumn('device_id'), function ($query) use ($serviceTemplate) {
                $query->select('device_id')
                ->from('service_templates_device')
                ->where('service_template_id', $serviceTemplate);
            }
        );
    }

    public function scopeNotInServiceTemplate($query, $serviceTemplate)
    {
        return $query->whereNotIn(
            $query->qualifyColumn('device_id'), function ($query) use ($serviceTemplate) {
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

    public function accessPoints(): HasMany
    {
        return $this->hasMany(AccessPoint::class, 'device_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(\App\Models\Alert::class, 'device_id');
    }

    public function alertLogs(): HasMany
    {
        return $this->hasMany(\App\Models\AlertLog::class, 'device_id');
    }

    public function alertSchedules(): MorphToMany
    {
        return $this->morphToMany(\App\Models\AlertSchedule::class, 'alert_schedulable', 'alert_schedulables', 'schedule_id', 'schedule_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(\App\Models\Application::class, 'device_id');
    }

    public function attribs(): HasMany
    {
        return $this->hasMany(\App\Models\DeviceAttrib::class, 'device_id');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(\App\Models\Availability::class, 'device_id');
    }

    public function bgppeers(): HasMany
    {
        return $this->hasMany(\App\Models\BgpPeer::class, 'device_id');
    }

    public function cefSwitching(): HasMany
    {
        return $this->hasMany(\App\Models\CefSwitching::class, 'device_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'device_relationships', 'parent_device_id', 'child_device_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(\App\Models\Component::class, 'device_id');
    }

    public function diskIo(): HasMany
    {
        return $this->hasMany(\App\Models\DiskIo::class, 'device_id');
    }

    public function hostResources(): HasMany
    {
        return $this->hasMany(HrDevice::class, 'device_id');
    }

    public function hostResourceValues(): HasOne
    {
        return $this->hasOne(HrSystem::class, 'device_id');
    }

    public function entityPhysical(): HasMany
    {
        return $this->hasMany(EntPhysical::class, 'device_id');
    }

    public function entityState(): HasMany
    {
        return $this->hasMany(EntityState::class, 'device_id');
    }

    public function eventlogs(): HasMany
    {
        return $this->hasMany(\App\Models\Eventlog::class, 'device_id', 'device_id');
    }

    public function graphs(): HasMany
    {
        return $this->hasMany(\App\Models\DeviceGraph::class, 'device_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\DeviceGroup::class, 'device_group_device', 'device_id', 'device_group_id');
    }

    public function ipsecTunnels(): HasMany
    {
        return $this->hasMany(IpsecTunnel::class, 'device_id');
    }

    public function ipv4(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\Ipv4Address::class, \App\Models\Port::class, 'device_id', 'port_id', 'device_id', 'port_id');
    }

    public function ipv6(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\Ipv6Address::class, \App\Models\Port::class, 'device_id', 'port_id', 'device_id', 'port_id');
    }

    public function isisAdjacencies(): HasMany
    {
        return $this->hasMany(\App\Models\IsisAdjacency::class, 'device_id', 'device_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(\App\Models\Link::class, 'local_device_id');
    }

    public function remoteLinks(): HasMany
    {
        return $this->hasMany(\App\Models\Link::class, 'remote_device_id');
    }

    public function allLinks(): \Illuminate\Support\Collection
    {
        return $this->links->merge($this->remoteLinks);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id', 'id');
    }

    public function macs(): HasMany
    {
        return $this->hasMany(Ipv4Mac::class, 'device_id');
    }

    public function mefInfo(): HasMany
    {
        return $this->hasMany(MefInfo::class, 'device_id');
    }

    public function muninPlugins(): HasMany
    {
        return $this->hasMany(\App\Models\MuninPlugin::class, 'device_id');
    }

    public function netscalerVservers(): HasMany
    {
        return $this->hasMany(NetscalerVserver::class, 'device_id');
    }

    public function ospfAreas(): HasMany
    {
        return $this->hasMany(\App\Models\OspfArea::class, 'device_id');
    }

    public function ospfInstances(): HasMany
    {
        return $this->hasMany(\App\Models\OspfInstance::class, 'device_id');
    }

    public function ospfNbrs(): HasMany
    {
        return $this->hasMany(\App\Models\OspfNbr::class, 'device_id');
    }

    public function ospfPorts(): HasMany
    {
        return $this->hasMany(\App\Models\OspfPort::class, 'device_id');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(\App\Models\Package::class, 'device_id', 'device_id');
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'device_relationships', 'child_device_id', 'parent_device_id');
    }

    public function perf(): HasMany
    {
        return $this->hasMany(\App\Models\DevicePerf::class, 'device_id');
    }

    public function ports(): HasMany
    {
        return $this->hasMany(\App\Models\Port::class, 'device_id', 'device_id');
    }

    public function portsAdsl(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\PortAdsl::class, \App\Models\Port::class, 'device_id', 'port_id');
    }

    public function portsFdb(): HasMany
    {
        return $this->hasMany(\App\Models\PortsFdb::class, 'device_id', 'device_id');
    }

    public function portsNac(): HasMany
    {
        return $this->hasMany(\App\Models\PortsNac::class, 'device_id', 'device_id');
    }

    public function portsStp(): HasMany
    {
        return $this->hasMany(\App\Models\PortStp::class, 'device_id', 'device_id');
    }

    public function portsVdsl(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\PortVdsl::class, \App\Models\Port::class, 'device_id', 'port_id');
    }

    public function portsVlan(): HasMany
    {
        return $this->hasMany(\App\Models\PortVlan::class, 'device_id', 'device_id');
    }

    public function processes(): HasMany
    {
        return $this->hasMany(\App\Models\Process::class, 'device_id');
    }

    public function processors(): HasMany
    {
        return $this->hasMany(\App\Models\Processor::class, 'device_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class, 'device_id');
    }

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\AlertRule::class, 'alert_device_map', 'device_id', 'rule_id');
    }

    public function sensors(): HasMany
    {
        return $this->hasMany(\App\Models\Sensor::class, 'device_id');
    }

    public function serviceTemplates(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\ServiceTemplate::class, 'service_templates_device', 'device_id', 'service_template_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(\App\Models\Service::class, 'device_id');
    }

    public function storage(): HasMany
    {
        return $this->hasMany(\App\Models\Storage::class, 'device_id');
    }

    public function stpInstances(): HasMany
    {
        return $this->hasMany(Stp::class, 'device_id');
    }

    public function stpPorts(): HasMany
    {
        return $this->hasMany(\App\Models\PortStp::class, 'device_id');
    }

    public function mempools(): HasMany
    {
        return $this->hasMany(\App\Models\Mempool::class, 'device_id');
    }

    public function mplsLsps(): HasMany
    {
        return $this->hasMany(\App\Models\MplsLsp::class, 'device_id');
    }

    public function mplsLspPaths(): HasMany
    {
        return $this->hasMany(\App\Models\MplsLspPath::class, 'device_id');
    }

    public function mplsSdps(): HasMany
    {
        return $this->hasMany(\App\Models\MplsSdp::class, 'device_id');
    }

    public function mplsServices(): HasMany
    {
        return $this->hasMany(\App\Models\MplsService::class, 'device_id');
    }

    public function mplsSaps(): HasMany
    {
        return $this->hasMany(\App\Models\MplsSap::class, 'device_id');
    }

    public function mplsSdpBinds(): HasMany
    {
        return $this->hasMany(\App\Models\MplsSdpBind::class, 'device_id');
    }

    public function mplsTunnelArHops(): HasMany
    {
        return $this->hasMany(\App\Models\MplsTunnelArHop::class, 'device_id');
    }

    public function mplsTunnelCHops(): HasMany
    {
        return $this->hasMany(\App\Models\MplsTunnelCHop::class, 'device_id');
    }

    public function outages(): HasMany
    {
        return $this->hasMany(DeviceOutage::class, 'device_id');
    }

    public function printerSupplies(): HasMany
    {
        return $this->hasMany(PrinterSupply::class, 'device_id');
    }

    public function pseudowires(): HasMany
    {
        return $this->hasMany(Pseudowire::class, 'device_id');
    }

    public function rServers(): HasMany
    {
        return $this->hasMany(LoadbalancerRserver::class, 'device_id');
    }

    public function slas(): HasMany
    {
        return $this->hasMany(Sla::class, 'device_id');
    }

    public function syslogs(): HasMany
    {
        return $this->hasMany(\App\Models\Syslog::class, 'device_id', 'device_id');
    }

    public function tnmsNeInfo(): HasMany
    {
        return $this->hasMany(TnmsneInfo::class, 'device_id');
    }

    public function users(): BelongsToMany
    {
        // FIXME does not include global read
        return $this->belongsToMany(\App\Models\User::class, 'devices_perms', 'device_id', 'user_id');
    }

    public function vminfo(): HasMany
    {
        return $this->hasMany(\App\Models\Vminfo::class, 'device_id');
    }

    public function vlans(): HasMany
    {
        return $this->hasMany(\App\Models\Vlan::class, 'device_id');
    }

    public function vrfLites(): HasMany
    {
        return $this->hasMany(\App\Models\VrfLite::class, 'device_id');
    }

    public function vrfs(): HasMany
    {
        return $this->hasMany(\App\Models\Vrf::class, 'device_id');
    }

    public function vServers(): HasMany
    {
        return $this->hasMany(LoadbalancerVserver::class, 'device_id');
    }

    public function wirelessSensors(): HasMany
    {
        return $this->hasMany(\App\Models\WirelessSensor::class, 'device_id');
    }
}
