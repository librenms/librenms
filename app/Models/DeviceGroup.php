<?php
/**
 * DeviceGroup.php
 *
 * Dynamic groups of devices
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use LibreNMS\Alerting\QueryBuilderFluentParser;
use Log;
use Permissions;

class DeviceGroup extends BaseModel
{
    public $timestamps = false;
    protected $fillable = ['name', 'desc', 'type'];
    protected $casts = ['rules' => 'array'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (DeviceGroup $deviceGroup) {
            $deviceGroup->devices()->detach();
        });

        static::saving(function (DeviceGroup $deviceGroup) {
            if ($deviceGroup->isDirty('rules')) {
                $deviceGroup->rules = $deviceGroup->getParser()->generateJoins()->toArray();
            }
        });

        static::saved(function (DeviceGroup $deviceGroup) {
            if ($deviceGroup->isDirty('rules')) {
                $deviceGroup->updateDevices();
            }
        });
    }

    // ---- Helper Functions ----

    /**
     * Update devices included in this group (dynamic only)
     */
    public function updateDevices()
    {
        if ($this->type == 'dynamic') {
            $this->devices()->sync(QueryBuilderFluentParser::fromJSON($this->rules)->toQuery()
                ->distinct()->pluck('devices.device_id'));
        }
    }

    /**
     * Update the device groups for the given device or device_id
     *
     * @param Device|int $device
     * @return array
     */
    public static function updateGroupsFor($device)
    {
        $device = ($device instanceof Device ? $device : Device::find($device));
        if (!$device instanceof Device) {
            // could not load device
            return [
                "attached" => [],
                "detached" => [],
                "updated" => [],
            ];
        }

        $device_group_ids = static::query()
            ->with(['devices' => function ($query) {
                $query->select('devices.device_id');
            }])
            ->get()
            ->filter(function ($device_group) use ($device) {
                /** @var DeviceGroup $device_group */
                if ($device_group->type == 'dynamic') {
                    try {
                        return $device_group->getParser()
                            ->toQuery()
                            ->where('devices.device_id', $device->device_id)
                            ->exists();
                    } catch (\Illuminate\Database\QueryException $e) {
                        Log::error("Device Group '$device_group->name' generates invalid query: " . $e->getMessage());
                        return false;
                    }
                }

                // for static, if this device is include, keep it.
                return $device_group->devices
                    ->where('device_id', $device->device_id)
                    ->isNotEmpty();
            })->pluck('id');

        return $device->groups()->sync($device_group_ids);
    }

    /**
     * Get a query builder parser instance from this device group
     *
     * @return QueryBuilderFluentParser
     */
    public function getParser()
    {
        return !empty($this->rules) ?
            QueryBuilderFluentParser::fromJson($this->rules) :
            QueryBuilderFluentParser::fromOld($this->pattern);
    }

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        return $query->whereIn('id', Permissions::deviceGroupsForUser($user));
    }

    // ---- Define Relationships ----

    public function devices()
    {
        return $this->belongsToMany('App\Models\Device', 'device_group_device', 'device_group_id', 'device_id');
    }

    public function services()
    {
        return $this->belongsToMany('App\Models\Service', 'device_group_device', 'device_group_id', 'device_id');
    }
}
