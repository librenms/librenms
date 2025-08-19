<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlertRuleRequest;
use App\Models\AlertRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Util\Time;

class AlertRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

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
            $this->syncTransports($request->input('transports', []), $alertRule);

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
    public function show(AlertRule $alertRule, Request $request): JsonResponse
    {
        $alertRule->load([
            'devices:device_id,hostname,sysName',
            'groups:id,name',
            'locations:id,location',
            'transportSingles:alert_transports.transport_id,transport_type,transport_name',
            'transportGroups:alert_transport_groups.transport_group_id,transport_group_name',
        ]);

        // FIXME: is this used?
        $templateId = $request->get('template_id');
        $builder = $alertRule->builder;
        if ($templateId) {
            $collection = json_decode(file_get_contents(resource_path('definitions/alert_rules.json')), true);
            if (isset($collection[$templateId])) {
                $builder = $collection[$templateId];
            }
        }

        return response()->json([
            'extra' => $alertRule->extra,
            'maps' => $this->formatDeviceMaps($alertRule),
            'transports' => $this->formatTransports($alertRule),
            'name' => $alertRule->name,
            'proc' => $alertRule->proc,
            'notes' => $alertRule->notes,
            'builder' => $builder,
            'severity' => $alertRule->severity,
            'adv_query' => $alertRule->query,
            'invert_map' => $alertRule->invert_map,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AlertRule $alertRule)
    {
        //
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
            $this->syncTransports($request->input('transports', []), $alertRule);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlertRule $alertRule)
    {
        //
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


    private function formatTransports(AlertRule $alertRule): array {
        $transports = [];

        foreach ($alertRule->transportSingles as $transport) {
            $transports[] = [
                'id' => $transport->transport_id,
                'text' => ucfirst($transport->transport_type) . ': ' . $transport->transport_name,
            ];
        }

        foreach ($alertRule->transportGroups as $group) {
            $transports[] = [
                'id' => 'g' . $group->transport_group_id,
                'text' => 'Group: ' . $group->transport_group_name,
            ];
        }

        return $transports;
    }

    private function fillAlertRule(AlertRule $alertRule, AlertRuleRequest $request): void
    {
        $data = $request->validated();

        $builderJson = $data['builder_json'] ?? '';
        $overrideQuery = $data['override_query'] ?? null;
        $advQuery = $data['adv_query'] ?? '';

        // build SQL query
        try {
            $query = $overrideQuery === 'on'
                ? $advQuery
                : ($builderJson !== '' ? QueryBuilderParser::fromJson($builderJson)->toSql() : '');
        } catch (\Throwable $e) {
            throw new \Exception('Invalid rule builder JSON: ' . $e->getMessage());
        }


        if ($overrideQuery !== 'on' && $builderJson === '') {
            throw new \Exception('No rule builder JSON provided');
        }

        $extra = [
            'mute' => $request->boolean('mute'),
            'count' => $data['count'] ?? '-1',
            'delay' => Time::durationToSeconds($data['delay'] ?? ''),
            'invert' => $request->boolean('invert'),
            'interval' => Time::durationToSeconds($data['interval'] ?? ''),
            'recovery' => $request->boolean('recovery'),
            'acknowledgement' => $request->boolean('acknowledgement'),
            'options' => [
                'override_query' => $overrideQuery,
            ],
        ];


        $name = $data['name'] ?? '';
        $alertRule->fill([
            'severity' => $data['severity'] ?? '',
            'extra' => $extra,
            'disabled' => false,
            'name' => $name,
            'proc' => $data['proc'] ?? '',
            'notes' => $data['notes'] ?? '',
            'query' => $query,
            'builder' => $builderJson !== '' ? json_decode($builderJson, true) : [],
            'invert_map' => $request->boolean('invert_map'),
        ]);
    }

    private function syncMaps(array $maps, AlertRule $alertRule): void
    {
        $deviceIds = [];
        $groupIds = [];
        $locationIds = [];
        foreach ($maps as $item) {
            if (Str::startsWith($item, 'l')) {
                $locationIds[] = (int) substr($item, 1);
            } elseif (Str::startsWith($item, 'g')) {
                $groupIds[] = (int) substr($item, 1);
            } else {
                $deviceIds[] = (int) $item;
            }
        }
        $alertRule->devices()->sync($deviceIds);
        $alertRule->groups()->sync($groupIds);
        $alertRule->locations()->sync($locationIds);
    }

     private function syncTransports(array $transports, AlertRule $alertRule): void
    {
        $transportIds = [];
        $transportGroupIds = [];
        foreach ($transports as $transport) {
            if (Str::startsWith($transport, 'g')) {
                $transportGroupIds[] = (int) substr($transport, 1);
            } else {
                $transportIds[] = (int) $transport;
            }
        }

        $alertRule->transportSingles()->syncWithPivotValues($transportIds, ['target_type' => 'single']);
        $alertRule->transportGroups()->syncWithPivotValues($transportGroupIds, ['target_type' => 'group']);
    }
}
