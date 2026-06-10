<?php

namespace App\Http\Controllers;

use App\Models\AlertTransport;
use App\Models\AlertOperationTransportMap;
use App\Models\TransportGroupTransport;
use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Alert\AlertData;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Exceptions;

class AlertTransportController extends Controller
{
    public function test(Request $request, AlertTransport $transport): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $transport);

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

    /**
     * Remove the specified alert transport from storage.
     *
     * @param  AlertTransport  $transport
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(AlertTransport $transport): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $transport);

        if ($transport->delete()) {
            AlertOperationTransportMap::where('transport_or_group_id', $transport->transport_id)
                ->where('target_type', 'single')
                ->delete();
            TransportGroupTransport::where('transport_id', $transport->transport_id)->delete();

            return response()->json([
                'status' => 'ok',
                'message' => 'Alert transport has been deleted',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Alert transport has not been deleted',
        ]);
    }
}
