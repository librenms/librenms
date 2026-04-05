<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlertOperationRequest;
use App\Models\AlertOperation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use LibreNMS\Enum\AlertRuleOperationPhase;

class AlertOperationController extends Controller
{
    /**
     * Web UI: list alert operations (named operations + segment summary).
     */
    public function index(): View
    {
        Gate::authorize('viewAny', AlertOperation::class);

        $operations = AlertOperation::query()
            ->withCount('alertRules')
            ->with([
                'segments.transportSingles:alert_transports.transport_id,transport_type,transport_name',
                'segments.transportGroups:alert_transport_groups.transport_group_id,transport_group_name',
            ])
            ->orderBy('name')
            ->get();

        return view('alert.operations.index', [
            'operations' => $operations,
        ]);
    }

    public function show(AlertOperation $alertOperation): JsonResponse
    {
        Gate::authorize('view', $alertOperation);

        $alertOperation->loadMissing([
            'segments.transportSingles:alert_transports.transport_id,transport_type,transport_name',
            'segments.transportGroups:alert_transport_groups.transport_group_id,transport_group_name',
        ]);

        return response()->json([
            'status' => 'ok',
            'operation' => $alertOperation->toApiArray(),
        ]);
    }

    public function store(AlertOperationRequest $request): JsonResponse
    {
        Gate::authorize('create', AlertOperation::class);

        try {
            $validated = $request->validated();
            $op = new AlertOperation;
            $op->name = $validated['name'];
            $op->default_operation_step_duration_seconds = $validated['default_operation_step_duration_seconds'] ?? null;
            $op->save();
            $this->syncSegments($op, $request);

            return response()->json([
                'status' => 'ok',
                'message' => 'Operation created',
                'id' => $op->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(AlertOperationRequest $request, AlertOperation $alertOperation): JsonResponse
    {
        Gate::authorize('update', $alertOperation);

        try {
            $validated = $request->validated();
            $alertOperation->name = $validated['name'];
            $alertOperation->default_operation_step_duration_seconds = $validated['default_operation_step_duration_seconds'] ?? null;
            $alertOperation->save();
            $this->syncSegments($alertOperation, $request);

            return response()->json([
                'status' => 'ok',
                'message' => 'Operation updated',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(AlertOperation $alertOperation): JsonResponse
    {
        Gate::authorize('delete', $alertOperation);

        if ($alertOperation->alertRules()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This operation is assigned to one or more alert rules and cannot be deleted.',
            ], 422);
        }

        $alertOperation->delete();

        return response()->json(['status' => 'ok', 'message' => __('Operation deleted')]);
    }

    private function syncSegments(AlertOperation $op, AlertOperationRequest $request): void
    {
        $rows = $request->validated('segments');
        $op->segments()->delete();

        foreach (array_values($rows) as $idx => $row) {
            $from = max(1, (int) ($row['escalation_step_from'] ?? 1));
            $toRaw = $row['escalation_step_to'] ?? null;
            $to = ($toRaw === '' || $toRaw === null) ? null : max($from, (int) $toRaw);

            $seg = $op->segments()->create([
                'position' => (int) ($row['position'] ?? $idx),
                'operation_phase' => AlertRuleOperationPhase::PROBLEM,
                'escalation_step_from' => $from,
                'escalation_step_to' => $to,
                'start_in_seconds' => max(0, (int) ($row['start_in_seconds'] ?? 0)),
                'step_duration_seconds' => max(0, (int) ($row['step_duration_seconds'] ?? 0)),
                'notifications_suppressed' => false,
            ]);

            $transportsRaw = $row['transports'] ?? [];
            if (! is_array($transportsRaw)) {
                $transportsRaw = [];
            }
            $transportsRaw = array_values(array_filter($transportsRaw, fn ($t) => $t !== null && $t !== ''));
            if ($transportsRaw === []) {
                throw new \InvalidArgumentException('Each segment must have at least one transport or transport group.');
            }

            $transportIds = [];
            $transportGroupIds = [];
            foreach ($transportsRaw as $transport) {
                if (Str::startsWith((string) $transport, 'g')) {
                    $transportGroupIds[] = (int) substr((string) $transport, 1);
                } else {
                    $transportIds[] = (int) $transport;
                }
            }

            $seg->transportSingles()->syncWithPivotValues($transportIds, ['target_type' => 'single']);
            $seg->transportGroups()->syncWithPivotValues($transportGroupIds, ['target_type' => 'group']);
        }
    }
}
