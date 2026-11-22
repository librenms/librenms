<?php

/**
 * app/Models/AlertRule.php
 *
 * Model for access to alert_rules table data
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

namespace App\Models;

use App\Facades\Permissions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Enum\AlertState;

/**
 * @property int $id
 * @property string $name
 * @property string $severity
 * @property array<string, mixed>|null $extra
 * @property bool|int $disabled
 * @property string|null $proc
 * @property string|null $notes
 * @property string $query
 * @property array<string, mixed>|null $builder
 * @property bool|int $invert_map
 * @property bool|int $notify_per_entity
 * @property int|null $max_entities
 * @property int|null $alert_operation_id
 * @property AlertOperation|null $alertOperation
 */
class AlertRule extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected static function booted(): void
    {
        static::deleting(function (AlertRule $rule): void {
            $rule->alerts()->delete();
            $rule->logs()->delete();
            AlertFault::where('rule_id', $rule->id)->delete();
            $rule->templateMaps()->delete();

            $rule->devices()->detach();
            $rule->groups()->detach();
            $rule->locations()->detach();
        });
    }

    protected $fillable = [
        'severity',
        'extra',
        'disabled',
        'name',
        'proc',
        'notes',
        'query',
        'builder',
        'invert_map',
        'notify_per_entity',
        'max_entities',
        'alert_operation_id',
    ];

    // query, builder and extra are NOT NULL without a database default,
    // so creates must always provide a value
    protected $attributes = [
        'query' => '',
        'builder' => '{}',
        'extra' => '{}',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'builder' => 'array',
            'extra' => 'array',
            'alert_operation_id' => 'integer',
            'notify_per_entity' => 'boolean',
            'max_entities' => 'integer',
        ];
    }

    // ---- Query scopes ----

    /**
     * @param  Builder<AlertRule>  $query
     * @return Builder<AlertRule>
     */
    protected function scopeHasAccess(Builder $query, User $user): Builder
    {
        $gate = Gate::forUser($user);
        if ($gate->allows('viewAll', self::class)) {
            return $query;
        }

        return $gate->allows('view', self::class)
            ? $query->mappedToAccessibleDevices($user)
            : $query->whereKey([]);
    }

    /**
     * Match rules with an accessible device in their device, group, or location mappings.
     *
     * @param  Builder<AlertRule>  $query
     * @return Builder<AlertRule>
     */
    protected function scopeMappedToAccessibleDevices(Builder $query, User $user): Builder
    {
        $deviceIds = Permissions::devicesForUser($user);
        $groupIds = Permissions::deviceGroupsForUser($user);

        return $query->where(fn (Builder $rules) => $rules
            ->whereHas('devices', fn (Builder $devices) => $devices->whereIntegerInRaw('devices.device_id', $deviceIds))
            ->orWhereHas('groups', fn (Builder $groups) => $groups->whereIntegerInRaw('device_groups.id', $groupIds))
            ->orWhereHas('locations.devices', fn (Builder $devices) => $devices->whereIntegerInRaw('devices.device_id', $deviceIds)));
    }

    /**
     * @param  Builder<AlertRule>  $query
     * @param  Device  $device
     * @return Builder<AlertRule>
     */
    protected function scopeForDevice(Builder $query, Device $device): Builder
    {
        return $query->where(function (Builder $query) use ($device): void {
            $query->where(function (Builder $query): void {
                $query->whereDoesntHave('devices')
                    ->whereDoesntHave('groups')
                    ->whereDoesntHave('locations');
            })->orWhere(function (Builder $query) use ($device): void {
                $query->where('invert_map', 0)
                    ->where(function (Builder $query) use ($device): void {
                        $query->whereHas('devices', fn ($q) => $q->where('devices.device_id', $device->device_id))
                            ->orWhereHas('groups.devices', fn ($q) => $q->where('devices.device_id', $device->device_id))
                            ->orWhereHas('locations', fn ($q) => $q->where('locations.id', $device->location_id));
                    });
            })->orWhere(function (Builder $query) use ($device): void {
                $query->where('invert_map', 1)
                    ->whereDoesntHave('devices', fn ($q) => $q->where('devices.device_id', $device->device_id))
                    ->whereDoesntHave('groups.devices', fn ($q) => $q->where('devices.device_id', $device->device_id))
                    ->whereDoesntHave('locations', fn ($q) => $q->where('locations.id', $device->location_id));
            });
        });
    }

    /**
     * @param  Builder<AlertRule>  $query
     * @return Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('alert_rules.disabled', 0);
    }

    /**
     * Scope for only alert rules that are currently in alarm
     *
     * @param  Builder<AlertRule>  $query
     * @return Builder
     */
    public function scopeIsActive($query)
    {
        return $query->enabled()
            ->join('alerts', 'alerts.rule_id', 'alert_rules.id')
            ->whereNotIn('alerts.state', [AlertState::CLEAR, AlertState::ACKNOWLEDGED, AlertState::RECOVERED]);
    }

    // ---- Define Relationships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Alert, $this>
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'rule_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\AlertLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AlertLog::class, 'rule_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\AlertTemplateMap, $this>
     */
    public function templateMaps(): HasMany
    {
        return $this->hasMany(AlertTemplateMap::class, 'alert_rule_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Device, $this>
     */
    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'alert_device_map', 'rule_id', 'device_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\DeviceGroup, $this>
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(DeviceGroup::class, 'alert_group_map', 'rule_id', 'group_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Location, $this>
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'alert_location_map', 'rule_id');
    }

    /**
     * @return BelongsTo<AlertOperation, $this>
     */
    public function alertOperation(): BelongsTo
    {
        return $this->belongsTo(AlertOperation::class, 'alert_operation_id');
    }

    /**
     * Backwards-compatible shape: one array entry per segment (same as legacy multi-row operations).
     *
     * @return array<int, array<string, mixed>>
     */
    public function toOperationsApiArray(): array
    {
        $this->load([
            'alertOperation.segments.transportSingles:alert_transports.transport_id,transport_type,transport_name',
            'alertOperation.segments.transportGroups:alert_transport_groups.transport_group_id,transport_group_name',
        ]);

        if ($this->alertOperation === null) {
            return [];
        }

        $out = [];
        foreach ($this->alertOperation->segments as $segment) {
            $out[] = array_merge($segment->toApiArray(), [
                'alert_operation_id' => $this->alertOperation->id,
                'name' => $this->alertOperation->name,
            ]);
        }

        return $out;
    }
}
