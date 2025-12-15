<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SyslogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate([
            'program' => 'nullable|string',
            'priority' => 'nullable|string',
            'device' => 'nullable|int',
            'from' => 'nullable|string',
            'to' => 'nullable|string',
            'level' => 'nullable|string',
        ]);

        $device = \App\Models\Device::hasAccess($request->user())
            ->select(['device_id', 'hostname', 'ip', 'sysName', 'display'])
            ->firstWhere('device_id', $request->input('device'));

        $syslog_program_filter = ['field' => 'program'];
        $syslog_priority_filter = ['field' => 'priority'];
        $device_selected = '';
        if ($device) {
            $device_selected = ['id' => $device->device_id, 'text' => $device->displayName()];
            $syslog_program_filter['device'] = $device->device_id;
            $syslog_priority_filter['device'] = $device->device_id;
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

        return view('syslog', [
            'device' => $device_selected,
            'filter' => [
                'device' => $device,
                'filter_device' => false,
                'now' => $now->format($format),
                'default_date' => $defaultFrom->format($format),
                'program' => $request->input('program', ''),
                'priority' => $request->input('priority', ''),
                'from' => $fromInput,
                'to' => $toInput,
            ],
            'syslog_program_filter' => $syslog_program_filter,
            'syslog_priority_filter' => $syslog_priority_filter,
        ]);
    }
}