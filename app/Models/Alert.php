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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LibreNMS\Enum\AlertState;

class Alert extends Model
{
    public $timestamps = false;

    /**
     * @return array{info: 'array'}
     */
    protected function casts(): array
    {
        return [
            'info' => 'array',
        ];
    }

    // ---- Query scopes ----

    /**
     * Scope a query to only include active alerts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('state', AlertState::ACTIVE);
    }

    /**
     * Scope a query to only include acknowledged alerts.
     */
    public function scopeAcknowledged(Builder $query): Builder
    {
        return $query->where('state', AlertState::ACKNOWLEDGED);
    }

    // ---- Define Relationships ----
    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    /**
     * @return BelongsTo<AlertRule, $this>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class, 'rule_id', 'id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'devices_perms', 'device_id', 'user_id');
    }
}
