<?php

/**
 * app/Models/Alert.php
 *
 * Model for access to alerts table data
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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Enum\AlertState;

class Alert extends DeviceRelatedModel
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'device_id',
        'rule_id',
        'state',
        'open',
        'alerted',
        'info',
        'timestamp',
        'note',
    ];

    /**
     * @return array{info: 'array'}
     */
    protected function casts(): array
    {
        return [
            'info' => 'array',
            'timestamp' => 'datetime',
        ];
    }

    // ---- Query scopes ----

    /**
     * Only select active alerts
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('state', '=', AlertState::ACTIVE);
    }

    /**
     * Only select active alerts
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeAcknowledged($query)
    {
        return $query->where('state', '=', AlertState::ACKNOWLEDGED);
    }

    // ---- Define Relationships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\AlertRule, $this>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'rule_id', 'id');
    }

    /**
     * All alert_log entries for this alert (matched by rule_id + device_id).
     *
     * Note: this relationship cannot be eager-loaded across a collection because
     * Eloquent only supports a single foreign key for HasMany, and the constraint
     * on rule_id uses whereColumn which is not available in eager-load queries.
     *
     * @return HasMany<AlertLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AlertLog::class, 'device_id', 'device_id')
            ->whereColumn('alert_log.rule_id', 'alerts.rule_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\AlertLog, $this>
     */
    public function latestLog(): BelongsTo
    {
        return $this->belongsTo(AlertLog::class, 'latest_alert_log_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'devices_perms', 'device_id', 'user_id');
    }
}
