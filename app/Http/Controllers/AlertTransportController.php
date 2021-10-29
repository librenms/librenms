<?php

namespace App\Http\Controllers;

use App\Models\AlertTransport;
use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Alert\AlertUtil;
use LibreNMS\Config;

class AlertTransportController extends Controller
{
    public function test(Request $request, AlertTransport $transport): \Illuminate\Http\JsonResponse
    {
        $device = Device::with('location')->first();
        $obj = [
            'hostname'  => $device->hostname,
            'device_id' => $device->device_id,
            'sysDescr' => $device->sysDescr,
            'version' => $device->version,
            'hardware' => $device->hardware,
            'location' => $device->location,
            'title' => 'Testing transport from ' . Config::get('project_name'),
            'elapsed'   => '11s',
            'alert_id'  => '000',
            'id'        => '000',
            'faults'    => false,
            'uid'       => '000',
            'severity'  => 'critical',
            'rule'      => 'macros.device = 1',
            'name'      => 'Test-Rule',
            'string'      => '#1: test => string;',
            'timestamp' => date('Y-m-d H:i:s'),
            'contacts'  => AlertUtil::getContacts($device->toArray()),
            'state'     => '1',
            'msg'       => 'This is a test alert',
        ];

        $opts = Config::get('alert.transports.' . $transport->transport_type);
        try {
            $result = $transport->instance()->deliverAlert($obj, $opts);

            if ($result === true) {
                return response()->json(['status' => 'ok']);
            }
        } catch (\Exception $e) {
            \Log::error($e);
            $result = basename($e->getFile(), '.php') . ':' . $e->getLine() . ' ' . $e->getMessage();
        }

        return response()->json([
            'status' => 'error',
            'message' => $result,
        ]);
    }
}
