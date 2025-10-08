<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use App\Models\Device;
use App\Models\DeviceGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AlertLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->validate($request, [
            'device_id' => 'nullable|int',
            'rule_id' => 'nullable|int',
            'device_group' => 'nullable|int',
            'state' => 'nullable|int',
            'min_severity' => 'nullable|int',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
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

        $device_group = DeviceGroup::hasAccess($request->user())->firstWhere('id', $request->input('device_group'));
        $device_group_selected = '';
        if ($device_group) {
            $device_group_selected = ['id' => $device_group->id, 'text' => $device_group->name];
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
            'device_group_selected' => $device_group_selected,
            'filter' => [
                'device_id' => $request->input('device_id') ?: null,
                'rule_id' => $request->input('rule_id') ?: null,
                'device_group' => $request->input('device_group') ?: null,
                'state' => $request->input('state', -1),
                'min_severity' => $request->input('min_severity'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ],
            'alert_states' => $alert_states,
            'alert_severities' => $alert_severities,
        ]);
    }
}
