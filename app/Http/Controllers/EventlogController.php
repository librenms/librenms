<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EventlogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->validate($request, [
            'eventtype' => 'nullable|string',
            'device' => 'nullable|int',
            'from' => 'nullable|string',
            'to' => 'nullable|string',
        ]);

        $device = \App\Models\Device::hasAccess($request->user())
            ->select(['device_id', 'hostname', 'ip', 'sysName', 'display'])
            ->firstWhere('device_id', $request->input('device'));

        $eventlog_filter = ['field' => 'type'];
        $device_selected = '';
        if ($device) {
            $device_selected = ['id' => $device->device_id, 'text' => $device->displayName()];
            $eventlog_filter['device'] = $device->device_id;
        }

        $format = LibrenmsConfig::get('dateformat.byminute', 'Y-m-d H:i');
        $now = Carbon::now();
        $defaultFrom = (clone $now)->subDays(1);
        $fromInput = $request->input('from');
        $toInput = $request->input('to');

        if (empty($fromInput) && empty($toInput)) {
            $fromInput = $defaultFrom->format($format);
            $toInput = $now->format($format);
        }

        return view('eventlog', [
            'device' => $device_selected,
            'filter' => [
                'now' => $now->format($format),
                'default_date' => $defaultFrom->format($format),
                'eventtype' => $request->input('eventtype', ''),
                'from' => $fromInput,
                'to' => $toInput,
                'device' => $device?->device_id,
            ],
            'eventlog_filter' => $eventlog_filter,
        ]);
    }
}
