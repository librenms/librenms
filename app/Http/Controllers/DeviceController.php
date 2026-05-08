<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
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

        if ($current_tab == 'port') {
            $vars = Url::parseLegacyPath($request->path());
            $port = $device->ports()->findOrFail($vars->get('port'));
            Gate::authorize('view', $port);
        } else {
            Gate::authorize('view', $device);
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
        $output = ob_get_clean();
        ob_end_clean();

        return $output;
    }

    public function rediscover(Device $device): JsonResponse
    {
        $device->last_discovered = null;
        $saved = $device->save();

        return response()->json([
            'message' => $saved ? 'Device scheduled for discovery' : 'Failed to schedule device for discovery',
            'status' => $saved ? 'ok' : 'error',
        ]);
    }

    public function deleteIndex(): View
    {
        $this->authorize('delete', Device::class);

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
