<?php

namespace App\Http\Controllers;

use App\Models\AlertOperationTransportMap;
use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AlertTransportGroupController extends Controller
{
    public function show(AlertTransportGroup $alertTransportGroup): JsonResponse
    {
        abort_unless(Gate::any(['view', 'update'], $alertTransportGroup), 403);

        return response()->json([
            'id' => $alertTransportGroup->transport_group_id,
            'name' => $alertTransportGroup->transport_group_name,
            'members' => $alertTransportGroup->transports()->get()->map(fn (AlertTransport $transport) => [
                'id' => $transport->transport_id,
                'text' => ucfirst((string) $transport->transport_type) . ': ' . $transport->transport_name,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', AlertTransportGroup::class);

        $group = $this->save($request, new AlertTransportGroup);

        return response()->json(['message' => __('Alert transport group :name created', ['name' => $group->transport_group_name])], 201);
    }

    public function update(Request $request, AlertTransportGroup $alertTransportGroup): JsonResponse
    {
        $this->authorize('update', $alertTransportGroup);

        $this->save($request, $alertTransportGroup);

        return response()->json(['message' => __('Alert transport group :name updated', ['name' => $alertTransportGroup->transport_group_name])]);
    }

    public function destroy(AlertTransportGroup $alertTransportGroup): JsonResponse
    {
        $this->authorize('delete', $alertTransportGroup);

        DB::transaction(function () use ($alertTransportGroup): void {
            $alertTransportGroup->transports()->detach();
            AlertOperationTransportMap::where('transport_or_group_id', $alertTransportGroup->transport_group_id)
                ->where('target_type', 'group')
                ->delete();
            $alertTransportGroup->delete();
        });

        return response()->json([
            'status' => 'ok',
            'message' => __('Alert transport group has been deleted'),
        ]);
    }

    private function save(Request $request, AlertTransportGroup $group): AlertTransportGroup
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|array|min:1',
            'members.*' => 'integer|exists:alert_transports,transport_id',
        ]);

        DB::transaction(function () use ($group, $validated): void {
            $group->transport_group_name = strip_tags($validated['name']);
            $group->save();
            $group->transports()->sync($validated['members']);
        });

        return $group;
    }
}
