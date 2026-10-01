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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<Sensor>
 */
class SensorController extends SelectController
{
    /**
     * Defines validation rules (will override base validation rules for select2 responses too)
     */
    protected function rules(): array
    {
        return [
            'device' => 'nullable|int',
            'sensor_class' => 'nullable|string',
        ];
    }

    /**
     * Defines search fields will be searched in order
     */
    protected function searchFields(Request $request): array
    {
        return ['sensor_descr', 'devices.hostname', 'devices.sysName'];
    }

    /**
     * Defines the base query for this resource
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Sensor::class);

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
     * @param  Sensor  $model
     * @return array{id: int|string, text: string, icon?: string, device_id?: int, sensor_class?: string}
     */
    public function formatItem(Model $model): array
    {
        $classDescr = ucfirst((string) $model->sensor_class);

        return [
            'id' => $model->sensor_id,
            'text' => $model->device->shortDisplayName() . ' - ' . $model->sensor_descr . ' (' . $classDescr . ')',
            'device_id' => $model->device_id,
            'sensor_class' => $model->sensor_class,
        ];
    }
}
