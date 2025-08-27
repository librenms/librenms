<?php

namespace App\Http\Controllers;

use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LibreNMS\Alert\AlertData;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Exceptions;

class AlertTransportController extends Controller
{
    public function test(Request $request, AlertTransport $transport): JsonResponse
    {
        /** @var Device $device */
        $device = Device::with('location')->first();
        $alert_data = AlertData::testData($device);

        try {
            $result = $transport->instance()->deliverAlert($alert_data);

            if ($result === true) {
                return response()->json(['status' => 'ok']);
            }
        } catch (AlertTransportDeliveryException $e) {
            return response()->json([
                'status' => 'error',
                'message' => strip_tags($e->getMessage()),
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            $result = basename($e->getFile(), '.php') . ':' . $e->getLine() . ' ' . $e->getMessage();

            // if non-fatal error, return ok
            if ($e instanceof \ErrorException && ! Exceptions::isFatalError($e)) {
                return response()->json([
                    'status' => 'ok',
                    'message' => strip_tags($result),
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => strip_tags($result),
        ]);
    }

    public function show(AlertTransport $transport): JsonResponse
    {
        $details = collect((array) $transport->transport_config)
            ->map(fn ($value, $key) => ['name' => $key, 'value' => $value])
            ->values()
            ->all();

        return response()->json([
            'name' => $transport->transport_name,
            'type' => $transport->transport_type,
            'is_default' => (bool) $transport->is_default,
            'details' => $details,
        ]);
    }

    public function groupMembers(AlertTransportGroup $group): JsonResponse
    {
        $members = DB::table('transport_group_transport as a')
            ->leftJoin('alert_transports as b', 'a.transport_id', '=', 'b.transport_id')
            ->where('a.transport_group_id', $group->getKey())
            ->get(['a.transport_id', 'b.transport_type', 'b.transport_name'])
            ->filter(fn ($member) => ! empty($member->transport_id))
            ->map(fn ($member) => [
                'id' => $member->transport_id,
                'text' => ucfirst($member->transport_type) . ': ' . $member->transport_name,
            ])
            ->values()
            ->all();

        return response()->json([
            'name' => $group->transport_group_name,
            'members' => $members,
        ]);
    }
}
