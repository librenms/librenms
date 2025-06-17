<?php

namespace App\Http\Controllers;

use App\Models\AlertTransport;
use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Alert\AlertData;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Exceptions;

class AlertTransportController extends Controller
{
    public function test(Request $request, AlertTransport $transport): \Illuminate\Http\JsonResponse
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
}
