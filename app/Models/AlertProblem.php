<?php

/**
 * app/Models/AlertProblem.php
 *
 * Model for access to alert_problems table data. A problem is a per-entity incident
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
class AlertProblem extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $table = 'alert_problems';

    protected $casts = [
        'info' => 'array',
        'details' => CompressedJson::class,
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
        'timestamp' => 'datetime',
    ];

    // ---- Query scopes ----

    /**
     * @param  Builder<AlertProblem>  $query
     * @return Builder<AlertProblem>
     */
    public function scopeOpen($query): Builder
    {
        return $query->where('open', 1);
    }

    /**
     * @param  Builder<AlertProblem>  $query
     * @return Builder<AlertProblem>
     */
    public function scopeActive($query): Builder
    {
        return $query->where('state', AlertState::ACTIVE);
    }

    /**
     * @param  Builder<AlertProblem>  $query
     * @return Builder<AlertProblem>
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
        return $this->hasMany(AlertLog::class, 'problem_id');
    }

    /**
     * The entity this problem is about (Port, Sensor, Device, ...), resolved via the morph map.
     *
     * @return MorphTo<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }
}
