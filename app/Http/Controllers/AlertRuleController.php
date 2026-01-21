<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Http\Requests\AlertRuleRequest;
use App\Models\AlertRule;
use App\Models\AlertTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use LibreNMS\Alerting\QueryBuilderFilter;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Util\Time;

class AlertRuleController extends Controller
{
    public function create(Request $request): View
    {
        $device_id = (int) $request->query('device_id', -1);
        $deviceName = DeviceCache::get($device_id)->displayName();
        $filters = json_encode(new QueryBuilderFilter('alert'));
        $defaults = [
            'default_severity' => LibrenmsConfig::get('alert_rule.severity'),
            'default_max_alerts' => LibrenmsConfig::get('alert_rule.max_alerts'),
            'default_delay' => LibrenmsConfig::get('alert_rule.delay') . 'm',
            'default_interval' => LibrenmsConfig::get('alert_rule.interval') . 'm',
            'default_mute_alerts' => LibrenmsConfig::get('alert_rule.mute_alerts'),
            'default_invert_rule_match' => LibrenmsConfig::get('alert_rule.invert_rule_match'),
            'default_recovery_alerts' => LibrenmsConfig::get('alert_rule.recovery_alerts'),
            'default_acknowledgement_alerts' => LibrenmsConfig::get('alert_rule.acknowledgement_alerts'),
            'default_invert_map' => LibrenmsConfig::get('alert_rule.invert_map'),
        ];

        // Alert rule collection (from definitions json)
        $collectionRules = [];
        $rawCollection = AlertRuleTemplateController::templatesCollection();
        $tmpId = 0;
        foreach ($rawCollection as $rule) {
            try {
                $sql = QueryBuilderParser::fromJson($rule['builder'] ?? [])->toSql(false);
            } catch (\Throwable) {
                $sql = '';
            }
            $collectionRules[] = [
                'id' => $tmpId++,
                'name' => $rule['name'] ?? ('Rule ' . $tmpId),
                'sql' => $sql,
            ];
        }

        // Existing DB rules for import
        $dbRules = AlertRule::query()->orderBy('name')->get()->map(function (AlertRule $rule) {
            $rule_display = '';
            if (! empty($rule->extra['options']['override_query'])) {
                $rule_display = 'Custom SQL Query';
            } else {
                try {
                    $rule_display = QueryBuilderParser::fromJson($rule->builder)->toSql(false);
                } catch (\Throwable) {
                }
            }

            return [
                'id' => $rule->id,
                'name' => $rule->name,
                'severity' => $rule->severity,
                'display' => $rule_display,
            ];
        })->all();

        return view('alerts.rules.create', array_merge([
            'device_id' => $device_id,
            'deviceName' => $deviceName,
            'filters' => $filters,
            'collectionRules' => $collectionRules,
            'dbRules' => $dbRules,
            'templates' => AlertTemplate::query()->orderBy('name')->get(['id', 'name']),
        ], $defaults));
    }

    public function edit(AlertRule $alertRule): View
    {
        $filters = json_encode(new QueryBuilderFilter('alert'));
        $defaults = [
            'default_severity' => LibrenmsConfig::get('alert_rule.severity'),
            'default_max_alerts' => LibrenmsConfig::get('alert_rule.max_alerts'),
            'default_delay' => LibrenmsConfig::get('alert_rule.delay') . 'm',
            'default_interval' => LibrenmsConfig::get('alert_rule.interval') . 'm',
            'default_mute_alerts' => LibrenmsConfig::get('alert_rule.mute_alerts'),
            'default_invert_rule_match' => LibrenmsConfig::get('alert_rule.invert_rule_match'),
            'default_recovery_alerts' => LibrenmsConfig::get('alert_rule.recovery_alerts'),
            'default_acknowledgement_alerts' => LibrenmsConfig::get('alert_rule.acknowledgement_alerts'),
            'default_invert_map' => LibrenmsConfig::get('alert_rule.invert_map'),
        ];

        // Alert rule collection (from definitions json)
        $collectionRules = [];
        $rawCollection = AlertRuleTemplateController::templatesCollection();
        $tmpId = 0;
        foreach ($rawCollection as $rule) {
            try {
                $sql = QueryBuilderParser::fromJson($rule['builder'] ?? [])->toSql(false);
            } catch (\Throwable) {
                $sql = '';
            }
            $collectionRules[] = [
                'id' => $tmpId++,
                'name' => $rule['name'] ?? ('Rule ' . $tmpId),
                'sql' => $sql,
            ];
        }

        // Existing DB rules for import
        $dbRules = AlertRule::query()->orderBy('name')->get()->map(function (AlertRule $rule) {
            $rule_display = '';
            if (! empty($rule->extra['options']['override_query'])) {
                $rule_display = 'Custom SQL Query';
            } else {
                try {
                    $rule_display = QueryBuilderParser::fromJson($rule->builder)->toSql(false);
                } catch (\Throwable) {
                }
            }

            return [
                'id' => $rule->id,
                'name' => $rule->name,
                'severity' => $rule->severity,
                'display' => $rule_display,
            ];
        })->all();

        return view('alerts.rules.edit', array_merge([
            'alertRule' => $alertRule,
            'filters' => $filters,
            'collectionRules' => $collectionRules,
            'dbRules' => $dbRules,
            'templates' => AlertTemplate::query()->orderBy('name')->get(['id', 'name']),
        ], $defaults));
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
            $this->syncTemplates((int) $request->input('template_id'), $request->input('template_transports', []), $alertRule);

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
            'transportSingles:alert_transports.transport_id,transport_type,transport_name',
            'transportGroups:alert_transport_groups.transport_group_id,transport_group_name',
        ]);

        return response()->json([
            'extra' => $alertRule->extra,
            'maps' => $this->formatDeviceMaps($alertRule),
            'transports' => $this->formatTransports($alertRule),
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
            $this->syncTransports($request->input('transports', []), $alertRule);
            $this->syncTemplates((int) $request->input('template_id'), $request->input('template_transports', []), $alertRule);

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

    private function formatTransports(AlertRule $alertRule): array
    {
        $transports = [];

        foreach ($alertRule->transportSingles as $transport) {
            $transports[] = [
                'id' => $transport->transport_id,
                'text' => ucfirst((string) $transport->transport_type) . ': ' . $transport->transport_name,
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

        // extra json
        $extra = $request->safe([
            'mute',
            'count',
            'invert',
            'acknowledgement',
            'recovery',
        ]);
        $extra['count'] ??= '-1';
        $extra['options'] = ['override_query' => $overrideQuery];
        $extra['delay'] = Time::durationToSeconds($request->validated('delay') ?? '');
        $extra['interval'] = Time::durationToSeconds($request->validated('interval') ?? '');
        $alertRule->extra = array_merge($alertRule->extra ?? [], $extra);
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

    private function syncTransports(array $transports, AlertRule $alertRule): void
    {
        $transportIds = [];
        $transportGroupIds = [];
        foreach ($transports as $transport) {
            if (Str::startsWith($transport, 'g')) {
                $transportGroupIds[] = (int) substr((string) $transport, 1);
            } else {
                $transportIds[] = (int) $transport;
            }
        }

        $alertRule->transportSingles()->syncWithPivotValues($transportIds, ['target_type' => 'single']);
        $alertRule->transportGroups()->syncWithPivotValues($transportGroupIds, ['target_type' => 'group']);
    }

    private function syncTemplates(?int $globalTemplateId, array $perTransportTemplates, AlertRule $alertRule): void
    {
        // Clear existing mappings for this rule
        DB::table('alert_template_map')->where('alert_rule_id', $alertRule->id)->delete();

        // Insert global template mapping (applies to all transports)
        if (! empty($globalTemplateId)) {
            DB::table('alert_template_map')->insert([
                'alert_templates_id' => $globalTemplateId,
                'alert_rule_id' => $alertRule->id,
                'transport_id' => null,
            ]);
        }

        // Insert per-transport mappings (only for single transports)
        foreach ($perTransportTemplates as $key => $templateId) {
            if (empty($templateId)) {
                continue;
            }

            // Only accept numeric transport ids (ignore transport groups like g123)
            if (! is_numeric($key)) {
                continue;
            }

            DB::table('alert_template_map')->insert([
                'alert_templates_id' => (int) $templateId,
                'alert_rule_id' => $alertRule->id,
                'transport_id' => (int) $key,
            ]);
        }
    }
}
