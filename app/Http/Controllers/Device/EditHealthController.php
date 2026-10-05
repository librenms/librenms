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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditHealthController
{
    use AuthorizesRequests;

    public function index(Device $device): View
    {
        $this->authorize('update', $device);
        $this->authorize('sensor.update');

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

    public function update(Request $request, Device $device, Sensor $sensor): JsonResponse
    {
        $this->authorize('update', $device);
        if (Gate::denies('update', $sensor)) {
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

        $sensor->forceFill($validated);

        if (isset($validated['sensor_custom'])) {
            // leave Reset for discovery, SensorObserver recalculates limits and clears the custom flag then
            $sensor->sensor_custom = 'Reset';
            $saved = $sensor->saveQuietly();
        } else {
            // SensorObserver turns Saving into Yes so later discovery does not overwrite the limits
            if (array_intersect_key($validated, array_flip(['sensor_limit', 'sensor_limit_warn', 'sensor_limit_low_warn', 'sensor_limit_low']))) {
                $sensor->sensor_custom = 'Saving';
            }
            $saved = $sensor->save();
        }

        if ($saved) {
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
        if (Gate::denies('sensor.update')) {
            return $this->unauthorized();
        }

        // leave Reset for discovery, SensorObserver recalculates limits and clears the custom flag then
        $count = $device->sensors()
            ->where('sensor_custom', 'Yes')
            ->update(['sensor_custom' => 'Reset']);

        return response()->json([
            'status' => 'ok',
            'message' => $count ? __('Custom limits removed') : __('No sensors to reset'),
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
