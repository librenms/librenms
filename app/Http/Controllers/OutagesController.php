<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OutagesController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'device' => 'nullable|int',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'status' => ['nullable', Rule::in(['current', 'previous', 'all'])],
            'preset' => ['nullable', Rule::in(['6h', '24h', '48h', '1w', '2w', '1m', '2m', '1y'])],
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

        $tz = $request->session()->get('preferences.timezone');
        $date_format = LibrenmsConfig::get('dateformat.byminute', 'Y-m-d H:i');

        return view('outages.index', [
            'device' => $device,
            'selected_device' => $selected_device,
            'from' => $from,
            'to' => $to,
            'status' => $request->input('status', 'current'),
            'preset' => $request->input('preset', true),
            'default_start_date' => Carbon::now($tz)->subMonth()->format($date_format),
            'default_end_date' => Carbon::now($tz)->format($date_format),
            'show_device_list' => true, // when html is shared with device tab
        ]);
    }
}
