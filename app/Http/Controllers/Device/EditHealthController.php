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

class EditHealthController
{
    public function index(Device $device): View
    {
        $sensors = $device->sensors()
            ->where('sensor_deleted', 0)
            ->orderBy('sensor_class')
            ->orderBy('sensor_type')
            ->orderBy('sensor_descr')
            ->get();

        return view('device.edit.health', [
            'device' => $device,
            'sensors' => $sensors,
            'ajaxPrefix' => 'sensor',
        ]);
    }

    public function reset(Device $device, Request $request): JsonResponse
    {
        $sensorIds = $request->input('sensor_id', []);

        if (! is_array($sensorIds) || empty($sensorIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid sensor id',
            ]);
        }

        $status = 'error';
        $message = 'Error resetting values';

        foreach ($sensorIds as $sensorId) {
            $sensor = $device->sensors()
                ->where('sensor_id', $sensorId)
                ->where('sensor_custom', '!=', 'No')
                ->first();

            if (! $sensor) {
                $message = 'Invalid sensor id';
                continue;
            }

            // Clear custom flag and allow discovery to manage limits again
            $sensor->sensor_custom = 'Resetting';

            if ($sensor->save()) {
                $message = 'Sensor values resetted';
                $status = 'ok';
            } else {
                $message = 'Could not reset sensors values';
            }
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function update(Device $device, Request $request): JsonResponse
    {
        $sensorId = $request->integer('sensor_id');
        $valueType = $request->input('value_type');
        $data = $request->input('data');

        $allowedColumns = [
            'sensor_limit',
            'sensor_limit_warn',
            'sensor_limit_low_warn',
            'sensor_limit_low',
        ];


        if (! in_array($valueType, $allowedColumns, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid value type',
            ]);
        }

        if (! $sensorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing sensor id',
            ]);
        }

        if ($data === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing data',
            ]);
        }

        $sensor = $device->sensors()->where('sensor_id', $sensorId)
            ->first();

        if (! $sensor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid sensor id',
            ]);
        }

        $sensor->{$valueType} = self::nullIfEmpty($data);
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

    public function updateAlert(Device $device, Request $request): JsonResponse
    {
        $subType = $request->input('sub_type');
        $sensorId = $request->integer('sensor_id');

        if (! $sensorId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid sensor id',
            ]);
        }

        $sensor = $device->sensors()->where('sensor_id', $sensorId)
            ->first();

        if (! $sensor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid sensor id',
            ]);
        }

        if ($subType) {
            $sensor->sensor_custom = 'Resetting';

            if ($sensor->save()) {
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

        $stateString = 'disabled';
        $rawState = $request->input('state');
        $state = filter_var($rawState, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($state === null) {
            $state = false;
        }

        if ($state) {
            $stateValue = 1;
            $stateString = 'enabled';
        } else {
            $stateValue = 0;
        }

        $sensor->sensor_alert = $stateValue;
        $sensorDesc = e($request->input('sensor_desc', ''));

        if ($sensor->save()) {
            return response()->json([
                'status' => $stateValue === 0 ? 'info' : 'ok',
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

