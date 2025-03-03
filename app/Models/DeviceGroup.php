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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LibreNMS\Alerting\QueryBuilderFluentParser;
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
            $this->devices()->sync(QueryBuilderFluentParser::fromJson($this->rules)->toQuery()
                ->distinct()->pluck('devices.device_id'));
        }
    }

    /**
     * Get a query builder parser instance from this device group
     *
     * @return QueryBuilderFluentParser
     */
    public function getParser()
    {
        return ! empty($this->rules) ?
            QueryBuilderFluentParser::fromJson($this->rules) :
            QueryBuilderFluentParser::fromOld($this->pattern);
    }

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        return $query->whereIntegerInRaw('id', Permissions::deviceGroupsForUser($user));
    }

    public function scopeInServiceTemplate($query, $serviceTemplate)
    {
        return $query->whereIn(
            $query->qualifyColumn('id'), function ($query) use ($serviceTemplate) {
                $query->select('device_group_id')
                    ->from('service_templates_device_group')
                    ->where('service_template_id', $serviceTemplate);
            }
        );
    }

    public function scopeNotInServiceTemplate($query, $serviceTemplate)
    {
        return $query->whereNotIn(
            $query->qualifyColumn('id'), function ($query) use ($serviceTemplate) {
                $query->select('device_group_id')
                    ->from('service_templates_device_group')
                    ->where('service_template_id', $serviceTemplate);
            }
        );
    }

    // ---- Define Relationships ----

    public function alertSchedules(): MorphToMany
    {
        return $this->morphToMany(\App\Models\AlertSchedule::class, 'alert_schedulable', 'alert_schedulables', 'schedule_id', 'schedule_id');
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Device::class, 'device_group_device', 'device_group_id', 'device_id');
    }

    public function services(): BelongsToMany
    {
        // $parentKey='id', $relatedKey='device_id' is required to generate the right SQL query.
        // Otherwise the primaryKey in Service.php will be used
        return $this->belongsToMany(\App\Models\Service::class, 'device_group_device', 'device_group_id', 'device_id', 'id', 'device_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'devices_group_perms', 'device_group_id', 'user_id');
    }

    public function serviceTemplates(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\ServiceTemplate::class, 'service_templates_device_group', 'device_group_id', 'service_template_id');
    }
}
