<?php

/**
 * EditHealthController.php
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
 * @copyright  2026 Neil Lathwood
 */

namespace App\Http\Controllers\Device;

use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditHealthController
{
    public function index(Device $device): View
    {
        Gate::authorize('view', Sensor::class);
        $sensors = $device->sensors()
            ->where('sensor_deleted', 0)
            ->orderBy('sensor_class')
            ->orderBy('sensor_type')
            ->orderBy('sensor_descr')
            ->get();

        return view('device.edit.health', [
            'device' => $device,
            'sensors' => $sensors,
        ]);
    }

    public function reset(Device $device, Request $request): JsonResponse
    {
        if (Gate::denies('update', Sensor::class)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }
        $validated = $request->validate([
            'sensor_id' => 'required|array|min:1',
            'sensor_id.*' => 'integer',
        ]);

        $sensorIds = $validated['sensor_id'];

        foreach ($sensorIds as $sensorId) {
            $sensor = $device->sensors()
                ->where('sensor_id', $sensorId)
                ->where('sensor_custom', '!=', 'No')
                ->first();

            if ($sensor) {
                // Clear custom flag and allow discovery to manage limits again
                $sensor->sensor_custom = 'Reset';

                if (! $sensor->saveQuietly()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Could not reset sensors values',
                    ]);
                }

                return response()->json([
                    'status' => 'ok',
                    'message' => 'Sensor values reset',
                ]);
            }
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'No sensors to reset',
        ]);
    }

    public function update(Device $device, Sensor $sensor, Request $request): JsonResponse
    {
        if (Gate::denies('update', Sensor::class)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }
        $validated = $request->validate([
            'value_type' => 'required|in:sensor_limit,sensor_limit_warn,sensor_limit_low_warn,sensor_limit_low',
            'data' => 'present',
        ]);

        $sensor->{$validated['value_type']} = self::nullIfEmpty($validated['data']);
        $sensor->sensor_custom = 'Saving';
        if ($sensor->save()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Sensor value updated',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Could not update sensor value',
        ]);
    }

    public function updateAlert(Device $device, Sensor $sensor, Request $request): JsonResponse
    {
        if (Gate::denies('update', Sensor::class)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }
        $validated = $request->validate([
            'sub_type' => 'nullable|in:remove-custom',
            'state' => 'nullable',
            'sensor_desc' => 'nullable|string',
        ]);

        $subType = $validated['sub_type'] ?? null;

        if ($subType) {
            $sensor->sensor_custom = 'Reset';

            if ($sensor->saveQuietly()) {
                return response()->json([
                    'status' => 'ok',
                    'message' => 'Custom limit removed. New one will be set up in rediscovery',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => "Couldn't remove custom limits. Enable debug and check logfile",
            ]);
        }

        $state = filter_var($validated['state'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $stateString = $state ? 'enabled' : 'disabled';
        $sensorDesc = e($validated['sensor_desc'] ?? '');
        $sensor->sensor_alert = $state;

        if ($sensor->save()) {
            return response()->json([
                'status' => $state ? 'ok' : 'info',
                'message' => 'Alerts ' . $stateString . ' for sensor ' . $sensorDesc,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Couldn\'t ' . substr($stateString, 0, -1) . ' alerts for sensor ' . $sensorDesc . '. Enable debug and check librenms.log',
        ]);
    }

    private static function nullIfEmpty(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }
}
