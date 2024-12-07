<?php
/**
 * DeviceRelatedModel.php
 *
 * -Description-
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use App\Facades\DeviceCache;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceRelatedModel extends BaseModel
{
    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    public function scopeInDeviceGroup($query, $deviceGroup)
    {
        // Build the list of device IDs in SQL
        $deviceIdsSubquery = \DB::table('device_group_device')
        ->where('device_group_id', $deviceGroup)
        ->pluck('device_id');

        // Use the result in the whereIn clause to avoid unoptimized subqueries
        // use whereIntegerInRaw to avoid a the defautl limit of 1000 for whereIn
        return $query->whereIntegerInRaw($query->qualifyColumn('device_id'), $deviceIdsSubquery);
    }

    // ---- Define Relationships ----

    public function device(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id', 'device_id');
    }

    // ---- Accessors/Mutators ----

    /**
     * Use cached device instance to load device relationships
     */
    public function getDeviceAttribute(): ?Device
    {
        if (! $this->relationLoaded('device')) {
            $device = DeviceCache::get($this->device_id);
            $this->setRelation('device', $device->exists ? $device : null);
        }

        return $this->getRelationValue('device');
    }
}
