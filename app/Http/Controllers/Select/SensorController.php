<?php

/**
 * SensorController.php
 *
 * Select2 controller for sensor selection with class filtering.
 * Used for multi-sensor graph aggregation.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 LibreNMS Contributors
 */

namespace App\Http\Controllers\Select;

use App\Models\Sensor;

class SensorController extends SelectController
{
    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'device' => 'nullable|int',
            'sensor_class' => 'nullable|string',
        ];
    }

    /**
     * Defines search fields will be searched in order
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function searchFields($request)
    {
        return ['sensor_descr', 'devices.hostname', 'devices.sysName'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = Sensor::hasAccess($request->user())
            ->has('device')
            ->with(['device' => function ($query): void {
                $query->select(['device_id', 'hostname', 'sysName', 'display']);
            }])
            ->select(['sensors.device_id', 'sensor_id', 'sensor_class', 'sensor_descr', 'sensor_type']);

        if ($request->input('term')) {
            // join with devices for searches
            $query->leftJoin('devices', 'devices.device_id', 'sensors.device_id');
        }

        // Filter by device if specified
        if ($device_id = $request->input('device')) {
            $query->where('sensors.device_id', $device_id);
        }

        // Filter by sensor class if specified (for intelligent filtering)
        if ($sensor_class = $request->input('sensor_class')) {
            $query->where('sensor_class', $sensor_class);
        }

        $query->orderBy('sensor_class')->orderBy('sensor_descr');

        return $query;
    }

    /**
     * Format a sensor item for Select2 display
     *
     * @param  Sensor  $sensor
     * @return array
     */
    public function formatItem($sensor)
    {
        /** @var Sensor $sensor */
        $classDescr = ucfirst((string) $sensor->sensor_class);

        return [
            'id' => $sensor->sensor_id,
            'text' => $sensor->device->shortDisplayName() . ' - ' . $sensor->sensor_descr . ' (' . $classDescr . ')',
            'device_id' => $sensor->device_id,
            'sensor_class' => $sensor->sensor_class,
        ];
    }
}
