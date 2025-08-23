<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AlertLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->validate($request, [
            'device_id' => 'nullable|int',
            'rule_id' => 'nullable|int',
            'state' => 'nullable|int',
            'min_severity' => 'nullable|int',
        ]);

        $device = Device::hasAccess($request->user())
            ->select(['device_id', 'hostname', 'ip', 'sysName', 'display'])
            ->firstWhere('device_id', $request->input('device_id'));

        $device_selected = '';
        if ($device) {
            $device_selected = ['id' => $device->device_id, 'text' => $device->displayName()];
        }

        $rule = AlertRule::firstWhere('id', $request->input('rule_id'));
        $rule_selected = '';
        if ($rule) {
            $rule_selected = ['id' => $rule->id, 'text' => $rule->name];
        }

        $alert_states = [
            'Any' => -1,
            'Ok (recovered)' => 0,
            'Alert' => 1,
            'Worse' => 3,
            'Better' => 4,
            'Changed' => 5,
        ];

        $alert_severities = [
            'Any' => '',
            'Ok, warning and critical' => 1,
            'Warning and critical' => 2,
            'Critical' => 3,
            'OK' => 4,
            'Warning' => 5,
        ];

        return view('alert-log', [
            'device_selected' => $device_selected,
            'rule_selected' => $rule_selected,
            'filter' => [
                'device_id' => $request->input('device_id') ?: null,
                'rule_id' => $request->input('rule_id') ?: null,
                'state' => $request->input('state', -1),
                'min_severity' => $request->input('min_severity'),
            ],
            'alert_states' => $alert_states,
            'alert_severities' => $alert_severities,
        ]);
    }
}
