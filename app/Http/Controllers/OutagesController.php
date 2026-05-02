<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OutagesController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'device' => 'nullable|int',
            'from' => 'nullable|date_or_relative',
            'to' => 'nullable|date_or_relative',
            'status' => ['nullable', Rule::in(['current', 'previous', 'all'])],
        ]);

        $device = null;
        $selected_device = null;

        if ($request->input('device')) {
            $device = Device::hasAccess($request->user())->find((int) $request->input('device'));

            if ($device) {
                $selected_device = ['id' => $device->device_id, 'text' => $device->displayName()];
            }
        }

        $from = $request->input('from');
        $to = $request->input('to');

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
