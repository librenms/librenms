<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlertRuleRequest;
use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Enum\AlertState;

class AlertRuleController extends Controller
{
    private const array PER_PAGE = [10, 50, 100, 250, 500, 1000, 5000];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AlertRule::class);

        return $this->listing($request);
    }

    /**
     * Rules that apply to a device, on the device settings page
     */
    public function device(Request $request, Device $device): View
    {
        $this->authorize('update', $device);
        $this->authorize('viewAny', AlertRule::class);

        return $this->listing($request, $device);
    }

    /**
     * Create the rules marked as default in the rule collection
     */
    public function createDefaults(): RedirectResponse
    {
        $this->authorize('create', AlertRule::class);

        $extra = ['mute' => false, 'count' => -1, 'delay' => 300, 'invert' => false, 'interval' => 300];

        foreach (resolve(AlertRuleTemplateController::class)->templatesCollection() as $rule) {
            if (empty($rule['default'])) {
                continue;
            }

            AlertRule::create([
                'name' => $rule['name'],
                'builder' => $rule['builder'],
                'query' => QueryBuilderParser::fromJson($rule['builder'])->toSql(),
                'severity' => 'critical',
                'extra' => array_replace($extra, json_decode($rule['extra'] ?? '[]', true) ?: []),
                'disabled' => 0,
            ]);
        }

        return redirect()->back();
    }

    private function listing(Request $request, ?Device $device = null): View
    {
        $perPage = (int) $request->input('per_page');
        $perPage = in_array($perPage, self::PER_PAGE) ? $perPage : 50;

        $visibleRules = AlertRule::hasAccess($request->user());
        $canCreate = Gate::allows('create', AlertRule::class);
        $rules = AlertRule::hasAccess($request->user())
            ->when($device, fn ($query) => $query->forDevice($device))
            ->with(['devices', 'groups', 'locations', 'alertOperation.segments.transportSingles', 'alertOperation.segments.transportGroups'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('alert-rules.index', [
            'device' => $device,
            'rules' => $rules,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            // the most severe alert state of each rule
            'states' => Alert::whereIn('rule_id', $rules->pluck('id'))
                ->groupBy('rule_id')
                ->selectRaw('rule_id, max(state) as state')
                ->pluck('state', 'rule_id'),
            'collection' => $canCreate ? resolve(AlertRuleTemplateController::class)->templatesCollection() : [],
            'copyableRules' => $canCreate ? $visibleRules->orderBy('name')->get() : collect(),
            'offerDefaults' => ! $device && $canCreate && $rules->total() === 0 && ! AlertRule::exists(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AlertRuleRequest $request): JsonResponse
    {
        $this->authorize('create', AlertRule::class);

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
        $this->authorize('view', $alertRule);

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
            'notify_per_entity' => $alertRule->notify_per_entity,
            'max_entities' => $alertRule->max_entities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AlertRuleRequest $request, AlertRule $alertRule): JsonResponse
    {
        $this->authorize('update', $alertRule);

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
        $this->authorize('update', $alertRule);

        $alertRule->disabled = ! $request->boolean('state', $alertRule->disabled);
        $success = $alertRule->save();

        if ($success && $alertRule->disabled) {
            AlertFault::where('rule_id', $alertRule->id)->where('open', 1)
                ->update(['open' => 0, 'state' => AlertState::RECOVERED]);
            Alert::where('rule_id', $alertRule->id)->get()->each(function (Alert $alert): void {
                $info = is_array($alert->info) ? $alert->info : [];
                unset($info['open_fault_count']);
                $alert->info = $info;
                $alert->update([
                    'state' => AlertState::CLEAR,
                    'open' => 0,
                    'alerted' => 0,
                ]);
            });
        }

        return response()->json(['status' => $success ? 200 : 422], $success ? 200 : 422);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlertRule $alertRule): JsonResponse
    {
        $this->authorize('delete', $alertRule);

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
                'text' => $device->display,
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
            'notify_per_entity',
            'max_entities',
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

        $extra = $request->safe([
            'invert',
            'acknowledgement',
            'recovery',
            'mute',
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
