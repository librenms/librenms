<?php

namespace App\Http\Controllers\Device\Tabs;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertLog;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Url;

class FaultsController extends Controller implements DeviceTab
{
    /** Filters that make no sense when the page is already limited to one device */
    private const DEVICE_FILTERS = ['device_id', 'device.location_id', 'device.groups.id'];

    public function visible(Device $device): bool
    {
        return Gate::allows('viewAny', Alert::class);
    }

    public function slug(): string
    {
        return 'faults';
    }

    public function icon(): string
    {
        return 'fa-exclamation-circle';
    }

    public function name(): string
    {
        return __('Faults');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function index(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        $this->authorize('viewAny', Alert::class);

        $request->validate(AlertFault::filterValidationRules());

        return view('device.tabs.faults.index', [
            'device' => $device,
            'filter' => $request->array('filter'),
            'filterFields' => $this->deviceFilterFields(AlertFault::filterFieldDefinitions()),
        ]);
    }

    public function history(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        $this->authorize('viewAny', AlertLog::class);

        $request->validate(AlertLog::filterValidationRules());

        return view('device.tabs.faults.history', [
            'device' => $device,
            'filter' => $request->array('filter'),
            'filterFields' => $this->deviceFilterFields(AlertLog::filterFieldDefinitions()),
        ]);
    }

    public function stats(Device $device): View
    {
        $this->authorize('view', $device);
        $this->authorize('viewAny', Alert::class);

        $dateFormat = LibrenmsConfig::get('alert_graph_date_format', '%Y-%m-%d');

        $stats = DB::table('alert_log')
            ->join('alert_rules', 'alert_log.rule_id', '=', 'alert_rules.id')
            ->where('alert_log.device_id', $device->device_id)
            ->where('alert_log.state', '!=', 0)
            ->selectRaw('DATE_FORMAT(time_logged, ?) as Date, COUNT(alert_log.rule_id) as totalCount, alert_rules.severity as Severity', [$dateFormat])
            ->groupBy('Date', 'alert_rules.severity')
            ->get();

        $groups = [];
        $items = [];
        foreach ($stats as $row) {
            $severity = (string) $row->Severity;
            $items[] = [
                'x' => (string) $row->Date,
                'y' => (int) $row->totalCount,
                'group' => $severity,
            ];
            if (! in_array($severity, $groups, true)) {
                $groups[] = $severity;
            }
        }

        $firstDate = array_first($items)['x'] ?? null;
        $lastDate = array_last($items)['x'] ?? null;

        return view('device.tabs.faults.stats', [
            'device' => $device,
            'groups' => $groups,
            'items' => $items,
            'zoom_max' => ($firstDate && $lastDate) ? abs(strtotime($firstDate) - strtotime($lastDate)) * 1000 : 0,
        ]);
    }

    /**
     * Old device/{device}/alerts, device/{device}/alerts/section=alert-log and device/{device}/alert-stats urls
     */
    public function legacy(Device $device, Request $request): RedirectResponse
    {
        if ($request->segment(3) == 'alert-stats') {
            $route = 'device.faults.stats';
        } else {
            $section = Url::parseLegacyPath($request->path())->get('section');
            $route = $section == 'alert-log' ? 'device.faults.history' : 'device.faults';
        }

        return redirect()->route($route, [
            'device' => $device->device_id,
            ...$request->query(),
        ]);
    }

    /**
     * @param  array<array{key: string}>  $fields
     * @return array<array{key: string}>
     */
    private function deviceFilterFields(array $fields): array
    {
        return array_values(array_filter($fields, fn ($field) => ! in_array($field['key'], self::DEVICE_FILTERS)));
    }
}
