<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class GraylogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate([
            'stream' => 'nullable|string',
            'device' => 'nullable|int',
            'range' => 'nullable|int',
            'loglevel' => 'nullable|int',
            'to' => 'nullable|string',
            'level' => 'nullable|string',
        ]);

        $device = \App\Models\Device::hasAccess($request->user())
            ->select(['device_id', 'hostname', 'ip', 'sysName', 'display'])
            ->firstWhere('device_id', $request->input('device'));

        $graylog_filter = ['field' => 'stream'];
        $device_selected = '';
        if ($device) {
            $device_selected = ['id' => $device->device_id, 'text' => $device->displayName()];
            $graylog_filter['device'] = $device->device_id;
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

        return view('graylog', [
            'device' => $device_selected,
            'filter' => [
                'timezone' => LibrenmsConfig::has('graylog.timezone'),
                'filter_device' => true,
                'show_form' => true,
                'stream' => $request->input('stream', ''),
                'range' => $request->input('range', '0'),
                'loglevel' => $request->input('loglevel', ''),
                'from' => $fromInput,
                'to' => $toInput,
                'default_date' => $defaultFrom->format($format),
                'now' => $now->format($format),
            ],
            'graylog_filter' => $graylog_filter,
        ]);
    }
}
