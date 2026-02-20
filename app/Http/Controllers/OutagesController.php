<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OutagesController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'device' => 'nullable|int',
            'from' => 'nullable|date_or_relative',
            'to' => 'nullable|date_or_relative',
            'status' => ['nullable', Rule::in(['current', 'previous', 'all'])],
        ]);

        $device_id = (int) $request->input('device');
        if ($device_id) {
            $device = Device::find($device_id);
            $selected_device = ['id' => $device->device_id, 'text' => $device->displayName()];
        } else {
            $device = null;
            $selected_device = null;
        }

        $from = $request->input('from');
        $to = $request->input('to');

        $date_format = LibrenmsConfig::get('dateformat.byminute', 'Y-m-d H:i');

        return view('outages.index', [
            'device' => $device,
            'selected_device' => $selected_device,
            'from' => $from,
            'to' => $to,
            'status' => $request->input('status', 'current'),
            'preset' => $request->input('preset', true),
            'show_device_list' => true, // when html is shared with device tab
        ]);
    }
}
