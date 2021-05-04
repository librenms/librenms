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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Enum\AlertState;

class AlertRule extends BaseModel
{
    public $timestamps = false;

    // ---- Query scopes ----

    /**
     * @param Builder<AlertRule> $query
     * @return Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('alert_rules.disabled', 0);
    }

    /**
     * Scope for only alert rules that are currently in alarm
     *
     * @param Builder<AlertRule> $query
     * @return Builder
     */
    public function scopeIsActive($query)
    {
        return $query->enabled()
            ->join('alerts', 'alerts.rule_id', 'alert_rules.id')
            ->whereNotIn('alerts.state', [AlertState::CLEAR, AlertState::ACKNOWLEDGED, AlertState::RECOVERED]);
    }

    /**
     * Scope to filter rules for devices permitted to user
     * (do not use for admin and global read-only users)
     *
     * @param Builder<AlertRule> $query
     * @param User $user
     * @return mixed
     */
    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        if (! $this->isJoined($query, 'alerts')) {
            $query->join('alerts', 'alerts.rule_id', 'alert_rules.id');
        }

        return $this->hasDeviceAccess($query, $user, 'alerts');
    }

    // ---- Define Relationships ----

    public function alerts(): HasMany
    {
        return $this->hasMany(\App\Models\Alert::class, 'rule_id');
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Device::class, 'alert_device_map', 'device_id', 'device_id');
    }
}
