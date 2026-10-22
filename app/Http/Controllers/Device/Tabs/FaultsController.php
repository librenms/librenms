<?php

namespace App\Http\Controllers\Device\Tabs;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AlertFault;
use App\Models\AlertLog;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Url;

class FaultsController extends Controller implements DeviceTab
{
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
            'filterFields' => AlertFault::filterFieldDefinitions($device->device_id),
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
            'filterFields' => AlertLog::filterFieldDefinitions($device->device_id),
        ]);
    }

    public function stats(Device $device): View
    {
        $this->authorize('view', $device);
        $this->authorize('viewAny', Alert::class);

        return view('device.tabs.faults.stats', [
            'device' => $device,
            'stats' => AlertLog::severityStats(AlertLog::where('alert_log.device_id', $device->device_id)),
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
}
