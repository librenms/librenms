<?php

namespace App\Http\Controllers;

use App\Actions\Alerts\ClearAlertRuleAlerts;
use App\Actions\Alerts\SaveAlertRule;
use App\Http\Interfaces\ToastInterface;
use App\Http\Requests\AlertRuleRequest;
use App\Http\Resources\AlertRuleEditor;
use App\Models\Alert;
use App\Models\AlertOperation;
use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Alerting\AlertRuleDefinitions;
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

    public function create(Request $request): View
    {
        $this->authorize('create', AlertRule::class);
        $selection = $request->validate([
            'template' => ['nullable', 'integer', 'min:0', 'prohibits:copy'],
            'copy' => ['nullable', 'integer', 'min:1'],
        ]);
        $draft = [];

        if (isset($selection['template'])) {
            $id = (int) $selection['template'];
            $definition = AlertRuleDefinitions::all()[$id] ?? abort(404);
            $draft = AlertRuleEditor::templateDraft($id, $definition);
        } elseif (isset($selection['copy'])) {
            $source = AlertRule::findOrFail($selection['copy']);
            $this->authorize('view', $source);
            $draft = AlertRuleEditor::copyDraft($source);
        }

        return $this->editor($request, new AlertRule, $draft);
    }

    public function edit(Request $request, AlertRule $alertRule): View
    {
        $this->authorize('update', $alertRule);
        $this->authorize('view', $alertRule);

        return $this->editor($request, $alertRule, $this->editorPayload($alertRule));
    }

    /** @param array<string, mixed> $draft */
    private function editor(Request $request, AlertRule $rule, array $draft): View
    {
        $request->validate(['device' => ['nullable', 'integer', 'min:1']]);
        $device = $this->editorDevice($request);
        $canCreate = Gate::allows('create', AlertRule::class);
        $maps = $draft['maps'] ?? ($device ? [['id' => $device->device_id, 'text' => $device->display]] : []);
        $operationId = $draft['alert_operation_id'] ?? null;
        $operationName = $draft['alert_operation_name'] ?? '';

        if ($request->session()->hasOldInput()) {
            $labels = array_column($maps, 'text', 'id');
            $maps = collect($request->old('maps', []))->filter(fn ($id) => is_string($id))
                ->map(fn ($id) => ['id' => $id, 'text' => $labels[$id] ?? $id])->values()->all();

            $oldOperation = filter_var($request->old('alert_operation_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;
            if ($oldOperation !== $operationId) {
                $operationId = $oldOperation;
                $operationName = $operationId ? AlertOperation::whereKey($operationId)->value('name') ?? (string) $operationId : '';
            }
        }

        return view('alert-rules.editor', [
            'rule' => $rule,
            'draft' => $draft,
            'device' => $device,
            'maps' => $maps,
            'operationId' => $operationId,
            'operationName' => $operationName,
            'backUrl' => $this->rulesUrl($device),
            'collection' => $canCreate ? AlertRuleDefinitions::all() : [],
            'copyableRules' => $canCreate ? AlertRule::hasAccess($request->user())->orderBy('name')->get() : collect(),
            'openCollection' => ! $rule->exists && $request->boolean('collection') && ! $request->session()->hasOldInput(),
        ]);
    }

    private function editorDevice(Request $request): ?Device
    {
        $id = $request->integer('device');
        if ($id === 0) {
            return null;
        }

        $device = Device::findOrFail($id);
        $this->authorize('update', $device);

        return $device;
    }

    private function rulesUrl(?Device $device): string
    {
        return $device ? route('device.edit.alert-rules', $device) : route('alert-rules.index');
    }

    /**
     * Create the rules marked as default in the rule collection
     */
    public function createDefaults(): RedirectResponse
    {
        $this->authorize('create', AlertRule::class);

        $extra = ['mute' => false, 'count' => -1, 'delay' => 300, 'invert' => false, 'interval' => 300];

        (new AlertRule)->getConnection()->transaction(function () use ($extra): void {
            foreach (AlertRuleDefinitions::all() as $rule) {
                if (empty($rule['default'])) {
                    continue;
                }

                AlertRule::firstOrCreate(['name' => $rule['name']], [
                    'builder' => $rule['builder'],
                    'query' => QueryBuilderParser::fromJson($rule['builder'])->toSql(),
                    'severity' => 'critical',
                    'extra' => array_replace($extra, $rule['extra']),
                    'disabled' => 0,
                ]);
            }
        });

        return redirect()->back();
    }

    private function listing(Request $request, ?Device $device = null): View
    {
        $perPage = (int) $request->input('per_page');
        $perPage = in_array($perPage, self::PER_PAGE) ? $perPage : 50;

        $user = $request->user();
        $canCreate = Gate::allows('create', AlertRule::class);
        $rules = AlertRule::hasAccess($user)
            ->when($device, fn ($query) => $query->forDevice($device))
            ->with(['devices', 'groups', 'locations', 'alertOperation.segments.transportSingles', 'alertOperation.segments.transportGroups'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $canViewAlerts = $user->can('viewAny', Alert::class);
        $statuses = $canViewAlerts ? Alert::hasAccess($user)
            ->whereIntegerInRaw('rule_id', $rules->pluck('id'))
            ->select('rule_id')
            ->selectRaw('MAX(CASE WHEN state IN (?, ?, ?, ?, ?) THEN 1 ELSE 0 END) AS alerting', [
                AlertState::ACTIVE, AlertState::ACKNOWLEDGED, AlertState::WORSE, AlertState::BETTER, AlertState::CHANGED,
            ])
            ->selectRaw('MAX(CASE WHEN state = ? THEN 1 ELSE 0 END) AS acknowledged', [AlertState::ACKNOWLEDGED])
            ->groupBy('rule_id')
            ->toBase()->get()->keyBy('rule_id') : collect();

        return view('alert-rules.index', [
            'device' => $device,
            'rules' => $rules,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'statuses' => $statuses,
            'canViewAlerts' => $canViewAlerts,
            'clearStatusTitle' => $user->can('viewAll', Alert::class) || $user->can('viewAll', Device::class)
                ? __('All devices matching this rule are OK')
                : __('No accessible devices matching this rule are alerting'),
            'offerDefaults' => ! $device && $canCreate && $rules->total() === 0 && ! AlertRule::exists(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AlertRuleRequest $request, SaveAlertRule $save, ToastInterface $toast): JsonResponse|RedirectResponse
    {
        return $this->saveResponse($request, new AlertRule, $save, $toast);
    }

    /**
     * Display the specified resource.
     */
    public function show(AlertRule $alertRule): JsonResponse
    {
        $this->authorize('view', $alertRule);

        return response()->json($this->editorPayload($alertRule));
    }

    /** @return array<string, mixed> */
    private function editorPayload(AlertRule $rule): array
    {
        $rule->load(['devices:device_id,display,hostname,sysName', 'groups:id,name', 'locations:id,location']);

        return ['maps' => $this->formatDeviceMaps($rule)] + AlertRuleEditor::make($rule)->resolve();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AlertRuleRequest $request, AlertRule $alertRule, SaveAlertRule $save, ToastInterface $toast): JsonResponse|RedirectResponse
    {
        return $this->saveResponse($request, $alertRule, $save, $toast);
    }

    private function saveResponse(AlertRuleRequest $request, AlertRule $alertRule, SaveAlertRule $save, ToastInterface $toast): JsonResponse|RedirectResponse
    {
        $device = $this->editorDevice($request);
        $editing = $alertRule->exists;
        try {
            $rule = $save->execute($alertRule, $request->ruleAttributes(), $request->mappingIds());
        } catch (\Exception $exception) {
            report($exception);
            $message = __('This alert rule could not be saved');

            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 500)
                : back()->withInput()->withErrors(['rule' => $message]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => ($editing ? 'Edited Rule: ' : 'Added Rule: ') . '<i>' . e($rule->name) . '</i>',
            ]);
        }

        $toast->success($editing ? __('Alert rule :name updated', ['name' => $rule->name]) : __('Alert rule :name created', ['name' => $rule->name]));

        return redirect($this->rulesUrl($device));
    }

    public function toggle(Request $request, AlertRule $alertRule, ClearAlertRuleAlerts $clearAlerts): JsonResponse
    {
        $this->authorize('update', $alertRule);

        $alertRule->disabled = ! $request->boolean('state', $alertRule->disabled);
        $success = $alertRule->save();

        if ($success && $alertRule->disabled) {
            $clearAlerts->execute($alertRule);
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
}
