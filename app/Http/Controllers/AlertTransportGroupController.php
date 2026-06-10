<?php

namespace App\Http\Controllers;

use App\Models\AlertTransportGroup;
use App\Models\AlertOperationTransportMap;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AlertTransportGroupController extends Controller
{
    /**
     * Remove the specified alert transport group from storage.
     *
     * @param  AlertTransportGroup  $alertTransportGroup
     * @return JsonResponse
     */
    public function destroy(AlertTransportGroup $alertTransportGroup): JsonResponse
    {
        if (Gate::denies('alert-transport.delete')) {
            return response()->json([
                'status' => 'error',
                'message' => 'ERROR: You need permission.',
            ]);
        }

        if ($alertTransportGroup->delete()) {
            $alertTransportGroup->transports()->detach();
            AlertOperationTransportMap::where('transport_or_group_id', $alertTransportGroup->transport_group_id)
                ->where('target_type', 'group')
                ->delete();

            return response()->json([
                'status' => 'ok',
                'message' => 'Alert transport group has been deleted',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'ERROR: Alert transport group has not been deleted',
        ]);
    }
}
