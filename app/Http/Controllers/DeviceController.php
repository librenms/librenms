<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use App\View\Components\Device\PageTabs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use LibreNMS\Util\Url;

class DeviceController
{
    use AuthorizesRequests;

    public function index(Request $request, string $device): View
    {
        $device = DeviceCache::get($device);
        abort_unless($device->exists, 404);
        Gate::authorize('view', $device);
        DeviceCache::setPrimary($device->device_id);

        $current_tab = $request->route('tab', 'overview');
        $tab_controller = PageTabs::getTab($current_tab);

        return view('device.tabs.' . $current_tab, [
            'title' => $tab_controller->name(),
            'device' => $device,
            'device_id' => $device->device_id,
            'data' => $tab_controller->data($device, $request),
            'current_tab' => $current_tab,
            'request' => $request,
        ]);
    }

    /**
     * Redirect legacy device urls to the tab's own route
     * Handles /device/device=1/tab=ports/view=detail/, /device/{hostname} and /device/1/ports/detail
     */
    public function legacy(Request $request, string $device, string $tab = 'overview', string $vars = ''): RedirectResponse
    {
        $device = DeviceCache::get(str_replace('device=', '', $device));
        abort_unless($device->exists, 404);

        $tab = str_replace('tab=', '', $tab) ?: 'overview';

        $port = null;
        if ($tab == 'port') {
            $port = $device->ports()->findOrFail($this->portId($vars));
            Gate::authorize('view', $port);
        } else {
            Gate::authorize('view', $device);
        }

        if ($tab === 'edit') {
            return $this->redirectToEditRoute($request, $device->device_id, $vars);
        }

        abort_unless(array_key_exists($tab, PageTabs::$tabsClasses), 404);

        return $this->redirectToTabRoute($request, $device->device_id, $tab, $vars, $port);
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
        // App filters can contain encoded slashes and plus signs. Split the raw path before decoding.
        $path = $tab === 'apps' ? $request->path() : $vars;
        $options = [];
        foreach (explode('/', $path) as $segment) {
            $segment = urldecode($segment);
            if (str_contains($segment, '=')) {
                [$key, $value] = explode('=', $segment, 2);
                if (! in_array($key, ['device', 'tab'], true) && ($key !== 'port' || $tab === 'apps')) {
                    $options[$key] = $value;
                }
            }
        }

        // sub views as a plain path segment: /ports/detail, /vlans/ports
        $first = explode('/', $vars)[0];
        if ($tab !== 'port' && $first !== '' && ! str_contains($first, '=')) {
            $options['view'] ??= $first;
        }

        $route = "device.$tab";
        if ($tab === 'ports' && ($options['view'] ?? null) === 'portsecurity') {
            $route = 'device.ports.security';
            unset($options['view']);
        }

        $params = ['device' => $deviceId] + ($port ? ['port' => $port->port_id] : []);

        return redirect()->route($route, $params + $request->query() + $options, 301);
    }

    private function redirectToEditRoute(Request $request, int $deviceId, string $vars): RedirectResponse
    {
        $legacy = Url::parseLegacyPath($vars)->all();
        $options = $request->query() + $legacy;
        $section = $legacy['section'] ?? $options['section'] ?? '';

        $route = match ($section) {
            '', 'default', 'device', 'edit' => 'device.edit',
            'alert-rules' => 'device.edit.alert-rules',
            'apps' => 'device.edit.apps',
            'component', 'components' => 'device.edit.components',
            'customoid', 'customoids' => 'device.edit.customoids',
            'health' => 'device.edit.health',
            'mempools' => 'device.edit.mempools',
            'misc' => 'device.edit.misc',
            'modules' => 'device.edit.modules',
            'snmp', 'ipmi', 'polling' => 'device.edit.polling',
            'ports' => 'device.edit.ports',
            'processors' => 'device.edit.processors',
            'routing' => 'device.edit.routing',
            'services' => 'device.edit.services',
            'storage' => 'device.edit.storage',
            'wireless-sensors' => 'device.edit.wireless-sensors',
            default => abort(404),
        };

        $tab = $options['tab'] ?? null;
        $params = match ($section) {
            'snmp', 'ipmi' => ['tab' => $section],
            'polling' => ['tab' => is_string($tab) ? $tab : null],
            'ports' => array_key_exists('filter', $options) ? ['filter' => $options['filter'] ?? ''] : [],
            'alert-rules' => Arr::only($options, ['page', 'per_page']),
            default => [],
        };

        return redirect()->route($route, ['device' => $deviceId] + $params, 301);
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
