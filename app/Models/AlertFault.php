<?php

/**
 * app/Models/AlertFault.php
 *
 * Model for access to alert_faults table data. A fault is a per-entity incident
 * created when an alert rule matches; its state changes are recorded in alert_log.
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
 */

namespace App\Models;

use App\Casts\CompressedJson;
use App\Models\Traits\AlertFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LibreNMS\Enum\AlertState;

/**
 * @property int $id
 * @property int $rule_id
 * @property int $device_id
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property string $entity_key
 * @property int $state
 * @property int $alerted
 * @property int $open
 * @property string|null $severity
 * @property string|null $note
 * @property array<string, mixed> $info
 * @property array<string, mixed> $details
 */
class AlertFault extends DeviceRelatedModel
{
    use AlertFilters;

    public $timestamps = false;
    protected $table = 'alert_faults';
    protected $fillable = [
        'rule_id',
        'device_id',
        'entity_type',
        'entity_id',
        'entity_key',
        'state',
        'alerted',
        'open',
        'severity',
        'note',
        'info',
        'details',
        'first_seen',
        'last_seen',
        'timestamp',
    ];

    protected $casts = [
        'info' => 'array',
        'details' => CompressedJson::class,
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
        'timestamp' => 'datetime',
    ];

    /** @var list<string> */
    protected array $filterable = [
        'search',
        'state',
        'rule_id',
        'rule.severity',
        'device_id',
        'device.location_id',
        'device.groups.id',
        'unreachable',
        'note',
    ];

    /**
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: array<string, string>, search?: bool}>
     */
    public static function filterFieldDefinitions(?int $deviceId = null): array
    {
        return [
            ...self::alertFilterFields($deviceId),
            [
                'key' => 'unreachable',
                'label' => __('Unreachable'),
                'type' => 'boolean',
            ],
            [
                'key' => 'note',
                'label' => __('Notes'),
                'type' => 'text',
            ],
        ];
    }

    /**
     * @return array<string, array{int, string}>
     */
    protected static function filterStates(): array
    {
        return [
            'alerted' => [AlertState::ACTIVE, __('Alerted')],
            'acknowledged' => [AlertState::ACKNOWLEDGED, __('Acknowledged')],
            'worse' => [AlertState::WORSE, __('Worse')],
            'better' => [AlertState::BETTER, __('Better')],
            'changed' => [AlertState::CHANGED, __('Changed')],
        ];
    }

    public function filterUnreachable(Builder $query, mixed $value, array $config): void
    {
        $this->scopeUnreachable($query, (bool) $value xor ($config['not'] ?? false));
    }

    // ---- Query scopes ----

    /**
     * Faults on devices whose parents are all down (the device is unreachable rather than failed).
     *
     * @param  Builder<AlertFault>  $query
     * @return Builder<AlertFault>
     */
    public function scopeUnreachable($query, bool $unreachable = true): Builder
    {
        $hasParent = fn ($q) => $q
            ->from('device_relationships')
            ->whereColumn('device_relationships.child_device_id', 'alert_faults.device_id');

        $hasUpParent = fn ($q) => $q
            ->from('device_relationships')
            ->join('devices as parent_devices', 'parent_devices.device_id', '=', 'device_relationships.parent_device_id')
            ->whereColumn('device_relationships.child_device_id', 'alert_faults.device_id')
            ->where('parent_devices.status', '!=', 0);

        return $unreachable
            ? $query->whereExists($hasParent)->whereNotExists($hasUpParent)
            : $query->where(fn ($q) => $q->whereNotExists($hasParent)->orWhereExists($hasUpParent));
    }

    /**
     * @param  Builder<AlertFault>  $query
     * @return Builder<AlertFault>
     */
    public function scopeOpen($query): Builder
    {
        return $query->where('open', 1);
    }

    /**
     * @param  Builder<AlertFault>  $query
     * @return Builder<AlertFault>
     */
    public function scopeActive($query): Builder
    {
        return $query->where('state', AlertState::ACTIVE);
    }

    /**
     * @param  Builder<AlertFault>  $query
     * @return Builder<AlertFault>
     */
    public function scopeAcknowledged($query): Builder
    {
        return $query->where('state', AlertState::ACKNOWLEDGED);
    }

    // ---- Define Relationships ----

    /**
     * @return BelongsTo<AlertRule, $this>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'rule_id', 'id');
    }

    /**
     * @return HasMany<AlertLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AlertLog::class, 'fault_id');
    }

    /**
     * The entity this fault is about (Port, Sensor, Device, ...), resolved via the morph map.
     *
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }
}
