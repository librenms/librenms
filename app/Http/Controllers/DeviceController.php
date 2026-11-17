<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Port;
use App\View\Components\Device\PageTabs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Url;

class DeviceController
{
    use AuthorizesRequests;

    /** Tabs with their own device.{tab} route, the legacy url only redirects to it */
    public const array ROUTED_TABS = ['graphs', 'health', 'loadbalancer', 'neighbours', 'netflow', 'port', 'pseudowires', 'qos', 'routing', 'slas', 'stp', 'wireless'];

    public function index(Request $request, $device, $current_tab = 'overview', $vars = '')
    {
        $device = str_replace('device=', '', $device);
        $device = DeviceCache::get($device);
        $device_id = $device->device_id;

        if (! $device->exists) {
            abort(404);
        }

        DeviceCache::setPrimary($device_id);

        $current_tab = str_replace('tab=', '', $current_tab) ?: 'overview';

        $port = null;
        if ($current_tab == 'port') {
            $port = $device->ports()->findOrFail($this->portId((string) $vars));
            Gate::authorize('view', $port);
        } else {
            Gate::authorize('view', $device);
        }

        if (in_array($current_tab, self::ROUTED_TABS, true)) {
            return $this->redirectToTabRoute($request, $device_id, $current_tab, (string) $vars, $port);
        }

        $tab_controller = PageTabs::getTab($current_tab);
        $title = $tab_controller->name();
        $data = $tab_controller->data($device, $request);

        $data_array = [
            'title' => $title,
            'device' => $device,
            'device_id' => $device_id,
            'data' => $data,
            'vars' => $vars,
            'current_tab' => $current_tab,
            'request' => $request,
        ];

        if (view()->exists('device.tabs.' . $current_tab)) {
            return view('device.tabs.' . $current_tab, $data_array);
        }

        $data_array['tab_content'] = $this->renderLegacyTab($current_tab, $device, $data);

        return view('device.tabs.legacy', $data_array);
    }

    /**
     * Port id from the native /port/{id} path or the legacy /port/port={id} path
     */
    private function portId(string $vars): int
    {
        $first = explode('/', $vars)[0];

        return (int) (ctype_digit($first) ? $first : Url::parseLegacyPath($vars)->get('port'));
    }

    /**
     * Send legacy urls to the tab's own route, /key=value/ path options move to the query string
     */
    private function redirectToTabRoute(Request $request, int $deviceId, string $tab, string $vars, ?Port $port): RedirectResponse
    {
        $options = [];
        foreach (explode('/', $vars) as $segment) {
            $segment = urldecode($segment);
            if (str_contains($segment, '=')) {
                [$key, $value] = explode('=', $segment, 2);
                if (! in_array($key, ['device', 'tab', 'port'], true)) {
                    $options[$key] = $value;
                }
            }
        }

        $params = ['device' => $deviceId] + ($port ? ['port' => $port->port_id] : []);

        return redirect()->route("device.$tab", $params + $request->query() + $options, 301);
    }

    private function renderLegacyTab($tab, Device $device, $data)
    {
        ob_start();
        $device = $device->toArray();
        $device['os_group'] = LibrenmsConfig::get("os.{$device['os']}.group");
        Debug::set(false);
        chdir(base_path());
        $init_modules = ['web', 'auth'];
        require base_path('/includes/init.php');

        $vars['device'] = $device['device_id'];
        $vars['tab'] = $tab;

        extract($data); // set preloaded data into variables
        include "includes/html/pages/device/$tab.inc.php";

        return ob_get_clean();
    }

    public function rediscover(Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $device->last_discovered = null;
        $saved = $device->save();

        return response()->json([
            'message' => $saved ? 'Device scheduled for discovery' : 'Failed to schedule device for discovery',
            'status' => $saved ? 'ok' : 'error',
            'discovery_pending' => $saved && ! $device->disabled,
        ]);
    }

    public function discoveryStatus(Device $device): JsonResponse
    {
        $this->authorize('view', $device);

        return response()->json([
            'discovered' => $device->last_discovered !== null,
        ]);
    }

    public function deleteIndex(): View
    {
        $this->authorize('device.delete');

        return view('device.delete', [
            'data_warn' => [
                __('Syslog'),
                __('Eventlog'),
                __('Alertlog'),
            ],
        ]);
    }

    public function deleteConfirm(Device $device): View
    {
        $this->authorize('delete', $device);

        return view('device.delete-confirm', [
            'device' => $device,
            'data_warn' => [
                __('Syslog'),
                __('Eventlog'),
                __('Alertlog'),
            ],
        ]);
    }

    public function destroy(Device $device): RedirectResponse
    {
        $this->authorize('delete', $device);

        $hostname = $device->hostname;
        $device->delete();

        return redirect()->route('device.delete')
            ->with('success', __('device.deleted', ['hostname' => $hostname]));
    }
}
