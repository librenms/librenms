<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlertOperationRequest;
use App\Models\AlertOperation;
use App\Services\Alerting\AlertOperationSyncer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

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
            $op->notifications_suppressed = (bool) ($validated['notifications_suppressed'] ?? false);
            $op->save();
            AlertOperationSyncer::sync($op, $request->validated('segments') ?? []);

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
            $alertOperation->notifications_suppressed = (bool) ($validated['notifications_suppressed'] ?? false);
            $alertOperation->save();
            AlertOperationSyncer::sync($alertOperation, $request->validated('segments') ?? []);

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
}
