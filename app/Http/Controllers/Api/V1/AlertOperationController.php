<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AlertOperation;
use App\Models\AlertOperationSegment;
use App\Models\AlertOperationTransportMap;
use App\Models\AlertTransport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Global alert operations over the v1 API: list them, and add or remove one
 * transport on their segments so an integration can receive every alert the
 * operations deliver without rewriting the rules that use them.
 */
class AlertOperationController extends Controller
{
    private const PHASES = ['problem', 'recovery', 'update'];

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', AlertOperation::class);

        $operations = AlertOperation::query()->with('segments')->orderBy('id')->get()
            ->map(fn (AlertOperation $operation) => $operation->toApiArray());

        return response()->json(['data' => $operations->values()->all()]);
    }

    /** The v1 route group does not substitute bindings, so ids are resolved here. */
    public function attachTransport(Request $request, int $operation): JsonResponse
    {
        $this->authorize('update', AlertOperation::class);
        $operation = AlertOperation::query()->findOrFail($operation);

        $validated = $request->validate([
            'transport_id' => 'required|integer|min:1|exists:alert_transports,transport_id',
            'phases' => 'sometimes|array|min:1',
            'phases.*' => 'in:problem,recovery,update',
        ]);
        $transport = AlertTransport::query()->findOrFail($validated['transport_id']);
        $phases = $validated['phases'] ?? self::PHASES;

        $added = 0;
        DB::transaction(function () use ($operation, $transport, $phases, &$added): void {
            foreach ($operation->segments()->whereIn('operation_phase', $phases)->get() as $segment) {
                /** @var AlertOperationSegment $segment */
                $exists = AlertOperationTransportMap::query()
                    ->where('segment_id', $segment->id)
                    ->where('target_type', 'single')
                    ->where('transport_or_group_id', $transport->transport_id)
                    ->exists();
                if (! $exists) {
                    AlertOperationTransportMap::create([
                        'segment_id' => $segment->id,
                        'transport_or_group_id' => $transport->transport_id,
                        'target_type' => 'single',
                    ]);
                    $added++;
                }
            }
        });

        return response()->json(['data' => $this->reload($operation), 'meta' => ['changed_segments' => $added]]);
    }

    public function detachTransport(int $operation, int $transport): JsonResponse
    {
        $this->authorize('update', AlertOperation::class);
        $operation = AlertOperation::query()->findOrFail($operation);
        $transport = AlertTransport::query()->findOrFail($transport);

        $removed = AlertOperationTransportMap::query()
            ->whereIn('segment_id', $operation->segments()->select('id'))
            ->where('target_type', 'single')
            ->where('transport_or_group_id', $transport->transport_id)
            ->delete();

        return response()->json(['data' => $this->reload($operation), 'meta' => ['changed_segments' => $removed]]);
    }

    /** @return array<string, mixed> */
    private function reload(AlertOperation $operation): array
    {
        $operation->unsetRelation('segments');

        return $operation->toApiArray();
    }
}
