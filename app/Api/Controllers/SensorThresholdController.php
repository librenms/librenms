<?php

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sensor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SensorThresholdController extends Controller
{
    private const THRESHOLD_FIELDS = [
        'sensor_limit_low',
        'sensor_limit_low_warn',
        'sensor_limit_warn',
        'sensor_limit',
    ];

    public function update(Sensor $sensor, Request $request): JsonResponse
    {
        $this->authorize('update', $sensor);
        abort_if($sensor->sensor_deleted, 404);

        $validated = $this->validateUpdate($request);
        $this->applyUpdate($sensor, $validated);
        $sensor->refresh();

        return $this->response(collect([$sensor]));
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sensor_ids' => 'required|array|min:1|max:500',
            'sensor_ids.*' => 'required|integer|distinct|min:1',
        ]);
        $update = $this->validateUpdate($request);
        $sensorIds = array_values($validated['sensor_ids']);
        $sensors = Sensor::query()
            ->whereIn('sensor_id', $sensorIds)
            ->where('sensor_deleted', 0)
            ->get()
            ->keyBy('sensor_id');

        $missing = array_values(array_diff($sensorIds, $sensors->keys()->all()));
        if ($missing !== []) {
            return response()->json([
                'status' => 'error',
                'message' => 'One or more sensors do not exist or have been deleted.',
                'missing_sensor_ids' => $missing,
            ], 404);
        }

        foreach ($sensors as $sensor) {
            $this->authorize('update', $sensor);
        }

        DB::transaction(function () use ($sensors, $update): void {
            foreach ($sensors as $sensor) {
                $this->applyUpdate($sensor, $update);
            }
        });

        return $this->response(Sensor::query()->whereIn('sensor_id', $sensorIds)->get());
    }

    /**
     * @return array<string, mixed>
     */
    private function validateUpdate(Request $request): array
    {
        $validated = $request->validate([
            'sensor_limit' => 'sometimes|nullable|numeric',
            'sensor_limit_warn' => 'sometimes|nullable|numeric',
            'sensor_limit_low_warn' => 'sometimes|nullable|numeric',
            'sensor_limit_low' => 'sometimes|nullable|numeric',
            'sensor_alert' => 'sometimes|boolean',
            'reset' => 'sometimes|boolean',
        ]);

        if (! collect(array_keys($validated))->contains(fn (string $key) => in_array($key, [...self::THRESHOLD_FIELDS, 'sensor_alert', 'reset'], true))) {
            throw ValidationException::withMessages([
                'thresholds' => ['At least one threshold, sensor_alert, or reset must be supplied.'],
            ]);
        }

        if (($validated['reset'] ?? false) && count($validated) > 1) {
            throw ValidationException::withMessages([
                'reset' => ['reset cannot be combined with threshold or alert changes.'],
            ]);
        }

        return $validated;
    }

    /**
     * @param  array<string, mixed>  $update
     */
    private function applyUpdate(Sensor $sensor, array $update): void
    {
        if ($update['reset'] ?? false) {
            $sensor->sensor_custom = 'Reset';
            $sensor->saveQuietly();

            return;
        }

        $changesThresholds = false;
        foreach (self::THRESHOLD_FIELDS as $field) {
            if (array_key_exists($field, $update)) {
                $sensor->{$field} = $update[$field] === null ? null : (float) $update[$field];
                $changesThresholds = true;
            }
        }

        if (array_key_exists('sensor_alert', $update)) {
            $sensor->sensor_alert = (bool) $update['sensor_alert'];
        }

        if ($changesThresholds) {
            $this->validateOrder($sensor);
            // SensorObserver converts Saving to Yes and preserves these values
            // during all subsequent discovery runs.
            $sensor->sensor_custom = 'Saving';
        }

        $sensor->save();
    }

    private function validateOrder(Sensor $sensor): void
    {
        $previous = null;
        foreach (self::THRESHOLD_FIELDS as $field) {
            $value = $sensor->{$field};
            if ($value === null) {
                continue;
            }

            if ($previous !== null && $value < $previous) {
                throw ValidationException::withMessages([
                    $field => ['Thresholds must be ordered: low <= low warning <= high warning <= high.'],
                ]);
            }
            $previous = $value;
        }
    }

    /**
     * @param  Collection<int, Sensor>  $sensors
     */
    private function response(Collection $sensors): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'count' => $sensors->count(),
            'sensors' => $sensors->map(fn (Sensor $sensor) => $sensor->only([
                'sensor_id',
                'device_id',
                'sensor_class',
                'sensor_descr',
                'sensor_current',
                ...self::THRESHOLD_FIELDS,
                'sensor_alert',
                'sensor_custom',
            ]))->values(),
        ]);
    }
}
