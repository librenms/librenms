<?php

/**
 * ServiceTemplate.php
 *
 * Service Templates with Dynamic groups of devices
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
 * @copyright  2020 Anthony F McInerney <bofh80>
 * @author     Anthony F McInerney <afm404@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LibreNMS\Alerting\QueryBuilderFluentParser;
use Log;

class ServiceTemplate extends BaseModel
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'ip',
        'check',
        'type',
        'rules',
        'desc',
        'param',
        'ignore',
        'status',
        'changed',
        'disabled',
        'name',
    ];

    protected $attributes = [ // default values
        'ignore' => '0',
        'disabled' => '0',
    ];

    protected $casts = [
        'ignore' => 'integer',
        'disabled' => 'integer',
        'rules' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (ServiceTemplate $template) {
            $template->devices()->detach();
            $template->groups()->detach();
        });

        static::saving(function (ServiceTemplate $template) {
            if ($template->type == 'dynamic' and $template->isDirty('rules')) {
                $template->rules = $template->getDeviceParser()->generateJoins()->toArray();
            }
        });

        static::saved(function (ServiceTemplate $template) {
            if ($template->type == 'dynamic' and $template->isDirty('rules')) {
                $template->updateDevices();
            }
        });
    }

    // ---- Helper Functions ----

    /**
     * Update devices included in this template (dynamic only)
     */
    public function updateDevices()
    {
        if ($this->type == 'dynamic') {
            $this->devices()->sync(QueryBuilderFluentParser::fromJSON($this->rules)->toQuery()
                ->distinct()->pluck('devices.device_id'));
        }
    }

    /**
     * Update the device template groups for the given device or device_id
     *
     * @param Device|int $device
     * @return array
     */
    public static function updateServiceTemplatesForDevice($device)
    {
        $device = ($device instanceof Device ? $device : Device::find($device));
        if (! $device instanceof Device) {
            // could not load device
            return [
                'attached' => [],
                'detached' => [],
                'updated' => [],
            ];
        }

        $template_ids = static::query()
            ->with(['devices' => function ($query) {
                $query->select('devices.device_id');
            }])
            ->get()
            ->filter(function ($template) use ($device) {
                /** @var ServiceTemplate $template */
                if ($template->type == 'dynamic') {
                    try {
                        return $template->getDeviceParser()
                            ->toQuery()
                            ->where('devices.device_id', $device->device_id)
                            ->exists();
                    } catch (\Illuminate\Database\QueryException $e) {
                        Log::error("Service Template '$template->name' generates invalid query: " . $e->getMessage());

                        return false;
                    }
                }

                // for static, if this device is include, keep it.
                return $template->devices
                    ->where('device_id', $device->device_id)
                    ->isNotEmpty();
            })->pluck('id');

        return $device->serviceTemplates()->sync($template_ids);
    }

    /**
     * Update the device template groups for the given device group or device_group_id
     *
     * @param DeviceGroup|int $deviceGroup
     * @return array
     */
    public static function updateServiceTemplatesForDeviceGroup($deviceGroup)
    {
        $deviceGroup = ($deviceGroup instanceof DeviceGroup ? $deviceGroup : DeviceGroup::find($deviceGroup));
        if (! $deviceGroup instanceof DeviceGroup) {
            // could not load device
            return [
                'attached' => [],
                'detached' => [],
                'updated' => [],
            ];
        }

        $template_ids = static::query()
            ->with(['device_groups' => function ($query) {
                $query->select('device_groups.id');
            }])
            ->get()
            ->filter(function ($template) use ($deviceGroup) {
                // for static, if this device group is include, keep it.
                return $template->groups
                    ->where('device_group_id', $deviceGroup->id)
                    ->isNotEmpty();
            })->pluck('id');

        return $deviceGroup->serviceTemplates()->sync($template_ids);
    }

    /**
     * Get a query builder parser instance from this Service Template device rule
     *
     * @return QueryBuilderFluentParser
     */
    public function getDeviceParser()
    {
        return QueryBuilderFluentParser::fromJson($this->rules);
    }

    // ---- Query Scopes ----

    /**
     * @param  Builder $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where('disabled', 1);
    }

    // ---- Define Relationships ----

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Device::class, 'service_templates_device', 'service_template_id', 'device_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Service::class, 'service_templates_device', 'service_template_id', 'device_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\DeviceGroup::class, 'service_templates_device_group', 'service_template_id', 'device_group_id');
    }
}
