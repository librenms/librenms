<?php

/**
 * EditWirelessSensorsController.php
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device;

use App\Models\Device;
use App\Models\WirelessSensor;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditWirelessSensorsController
{
    use AuthorizesRequests;

    /**
     * Clearing limits lets discovery set them again, WirelessSensorObserver only allows discovery to fill empty limits
     *
     * @var array<string, null>
     */
    private const CLEARED_LIMITS = [
        'sensor_limit' => null,
        'sensor_limit_warn' => null,
        'sensor_limit_low_warn' => null,
        'sensor_limit_low' => null,
    ];

    public function index(Device $device): View
    {
        $this->authorize('update', $device);
        $this->authorize('wireless-sensor.update');

        $sensors = $device->wirelessSensors()
            ->where('sensor_deleted', 0)
            ->orderBy('sensor_class')
            ->orderBy('sensor_type')
            ->orderBy('sensor_descr')
            ->get();

        return view('device.edit.wireless-sensors', [
            'device' => $device,
            'sensors' => $sensors,
        ]);
    }

    public function update(Request $request, Device $device, WirelessSensor $wirelessSensor): JsonResponse
    {
        $this->authorize('update', $device);
        if (Gate::denies('update', $wirelessSensor)) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'sensor_limit' => 'sometimes|nullable|numeric',
            'sensor_limit_warn' => 'sometimes|nullable|numeric',
            'sensor_limit_low_warn' => 'sometimes|nullable|numeric',
            'sensor_limit_low' => 'sometimes|nullable|numeric',
            'sensor_alert' => 'sometimes|boolean',
            'sensor_custom' => 'sometimes|in:No',
        ]);

        if (empty($validated)) {
            return response()->json([
                'status' => 'error',
                'message' => __('Nothing to update'),
            ], 422);
        }

        $wirelessSensor->fill($validated);

        if (isset($validated['sensor_custom'])) {
            $wirelessSensor->fill(self::CLEARED_LIMITS);
        } elseif (array_intersect_key($validated, self::CLEARED_LIMITS)) {
            // mark limits as custom so discovery does not overwrite them
            $wirelessSensor->sensor_custom = 'Yes';
        }

        // WirelessSensorObserver reverts limits when sensor_custom is Yes, skip it so user changes are saved
        if ($wirelessSensor->saveQuietly()) {
            return response()->json([
                'status' => 'ok',
                'message' => __('Sensor updated'),
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('Could not update sensor'),
        ]);
    }

    public function reset(Device $device): JsonResponse
    {
        $this->authorize('update', $device);
        if (Gate::denies('wireless-sensor.update')) {
            return $this->unauthorized();
        }

        $count = $device->wirelessSensors()
            ->where('sensor_custom', 'Yes')
            ->update(['sensor_custom' => 'No'] + self::CLEARED_LIMITS);

        return response()->json([
            'status' => 'ok',
            'message' => $count ? __('Custom limits removed, discovery will set default limits') : __('No sensors to reset'),
        ]);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => __('Unauthorized'),
        ], 403);
    }
}
