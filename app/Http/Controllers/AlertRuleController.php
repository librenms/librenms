<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlertRuleRequest;
use App\Models\AlertRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LibreNMS\Alerting\QueryBuilderParser;

class AlertRuleController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(AlertRuleRequest $request): JsonResponse
    {
        try {
            $alertRule = new AlertRule;
            $this->fillAlertRule($alertRule, $request);
            $alertRule->save();

            $this->syncMaps($request->input('maps', []), $alertRule);

            return response()->json([
                'status' => 'ok',
                'message' => 'Added Rule: <i>' . e($alertRule->name) . '</i>',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AlertRule $alertRule): JsonResponse
    {
        $alertRule->load([
            'devices:device_id,hostname,sysName',
            'groups:id,name',
            'locations:id,location',
            'alertOperation:id,name,default_operation_step_duration_seconds',
        ]);

        return response()->json([
            'extra' => $alertRule->extra,
            'maps' => $this->formatDeviceMaps($alertRule),
            'alert_operation_id' => $alertRule->alert_operation_id,
            'alert_operation_name' => $alertRule->alertOperation?->name,
            'operations' => $alertRule->toOperationsApiArray(),
            'default_operation_step_duration_seconds' => $alertRule->alertOperation?->default_operation_step_duration_seconds,
            'name' => $alertRule->name,
            'proc' => $alertRule->proc,
            'notes' => $alertRule->notes,
            'builder' => $alertRule->builder,
            'severity' => $alertRule->severity,
            'adv_query' => $alertRule->query,
            'invert_map' => $alertRule->invert_map,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AlertRuleRequest $request, AlertRule $alertRule): JsonResponse
    {
        try {
            $this->fillAlertRule($alertRule, $request);

            if (! $alertRule->save()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to edit Rule <i>' . e($alertRule->name) . '</i>',
                ]);
            }

            $this->syncMaps($request->input('maps', []), $alertRule);

            return response()->json([
                'status' => 'ok',
                'message' => 'Edited Rule: <i>' . e($alertRule->name) . '</i>',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function toggle(Request $request, AlertRule $alertRule): JsonResponse
    {
        $alertRule->disabled = ! $request->boolean('state', $alertRule->disabled);
        $success = $alertRule->save();
        url('graphs', ['id' => 42, 'type' => 'sensor_']);

        return response()->json(['status' => $success ? 200 : 422], $success ? 200 : 422);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlertRule $alertRule): JsonResponse
    {
        $success = $alertRule->delete();

        return response()->json(['status' => $success ? 200 : 422], $success ? 200 : 422);
    }

    /**
     * @return array<int, array{id: int|string, text: string}>
     */
    private function formatDeviceMaps(AlertRule $alertRule): array
    {
        $maps = [];

        foreach ($alertRule->devices as $device) {
            $maps[] = [
                'id' => $device->device_id,
                'text' => $device->displayName(),
            ];
        }

        foreach ($alertRule->groups as $group) {
            $maps[] = [
                'id' => 'g' . $group->id,
                'text' => $group->name,
            ];
        }

        foreach ($alertRule->locations as $location) {
            $maps[] = [
                'id' => 'l' . $location->id,
                'text' => $location->location,
            ];
        }

        return $maps;
    }

    private function fillAlertRule(AlertRule $alertRule, AlertRuleRequest $request): void
    {
        $alertRule->fill($request->safe([
            'severity',
            'name',
            'proc',
            'notes',
            'invert_map',
        ]));
        $alertRule->disabled ??= false;
        $alertRule->builder = json_decode((string) $request->validated('builder_json', '[]'), true);
        $overrideQuery = $request->validated('override_query');

        // build SQL query
        if ($overrideQuery) {
            $alertRule->query = $request->validated('adv_query');
        } elseif ($alertRule->builder) {
            try {
                $alertRule->query = QueryBuilderParser::fromJson($alertRule->builder)->toSql();
            } catch (\Throwable $e) {
                throw new \Exception('Invalid rule builder JSON: ' . $e->getMessage());
            }
        }

        // extra json (no delay/interval/count/mute — those live on operations)
        $extra = $request->safe([
            'invert',
            'acknowledgement',
            'recovery',
        ]);
        $extra['options'] = ['override_query' => $overrideQuery];
        $alertRule->extra = array_merge($alertRule->extra ?? [], $extra);

        $opId = $request->input('alert_operation_id');
        $alertRule->alert_operation_id = ($opId === null || $opId === '') ? null : (int) $opId;
    }

    private function syncMaps(array $maps, AlertRule $alertRule): void
    {
        $deviceIds = [];
        $groupIds = [];
        $locationIds = [];
        foreach ($maps as $item) {
            if (Str::startsWith($item, 'l')) {
                $locationIds[] = (int) substr((string) $item, 1);
            } elseif (Str::startsWith($item, 'g')) {
                $groupIds[] = (int) substr((string) $item, 1);
            } else {
                $deviceIds[] = (int) $item;
            }
        }
        $alertRule->devices()->sync($deviceIds);
        $alertRule->groups()->sync($groupIds);
        $alertRule->locations()->sync($locationIds);
    }
}
