<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\View\Components\Device\PageTabs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Url;

class DeviceController
{
<<<<<<< HEAD
    private $tabs = [
        'overview' => \App\Http\Controllers\Device\Tabs\OverviewController::class,
        'graphs' => \App\Http\Controllers\Device\Tabs\GraphsController::class,
        'health' => \App\Http\Controllers\Device\Tabs\HealthController::class,
        'apps' => \App\Http\Controllers\Device\Tabs\AppsController::class,
        'processes' => \App\Http\Controllers\Device\Tabs\ProcessesController::class,
        'collectd' => \App\Http\Controllers\Device\Tabs\CollectdController::class,
        'munin' => \App\Http\Controllers\Device\Tabs\MuninController::class,
        'ports' => \App\Http\Controllers\Device\Tabs\PortsController::class,
        'port' => \App\Http\Controllers\Device\Tabs\PortController::class,
        'slas' => \App\Http\Controllers\Device\Tabs\SlasController::class,
        'wireless' => \App\Http\Controllers\Device\Tabs\WirelessController::class,
        'accesspoints' => \App\Http\Controllers\Device\Tabs\AccessPointsController::class,
        'vlans' => \App\Http\Controllers\Device\Tabs\VlansController::class,
        'vm' => \App\Http\Controllers\Device\Tabs\VmInfoController::class,
        'mef' => \App\Http\Controllers\Device\Tabs\MefController::class,
        'tnmsne' => \App\Http\Controllers\Device\Tabs\TnmsneController::class,
        'loadbalancer' => \App\Http\Controllers\Device\Tabs\LoadBalancerController::class,
        'routing' => \App\Http\Controllers\Device\Tabs\RoutingController::class,
        'pseudowires' => \App\Http\Controllers\Device\Tabs\PseudowiresController::class,
        'neighbours' => \App\Http\Controllers\Device\Tabs\NeighboursController::class,
        'stp' => \App\Http\Controllers\Device\Tabs\StpController::class,
        'packages' => \App\Http\Controllers\Device\Tabs\PackagesController::class,
        'inventory' => \App\Http\Controllers\Device\Tabs\InventoryController::class,
        'services' => \App\Http\Controllers\Device\Tabs\ServicesController::class,
        'printer' => \App\Http\Controllers\Device\Tabs\PrinterController::class,
        'logs' => \App\Http\Controllers\Device\Tabs\LogsController::class,
        'alerts' => \App\Http\Controllers\Device\Tabs\AlertsController::class,
        'alert-stats' => \App\Http\Controllers\Device\Tabs\AlertStatsController::class,
        'showconfig' => \App\Http\Controllers\Device\Tabs\ShowConfigController::class,
        'netflow' => \App\Http\Controllers\Device\Tabs\NetflowController::class,
        'qos' => \App\Http\Controllers\Device\Tabs\QosController::class,
        'latency' => \App\Http\Controllers\Device\Tabs\LatencyController::class,
        'alphabridge' => \App\Http\Controllers\Device\Tabs\alphabridgeController::class,
        'nac' => \App\Http\Controllers\Device\Tabs\NacController::class,
        'notes' => \App\Http\Controllers\Device\Tabs\NotesController::class,
        'edit' => \App\Http\Controllers\Device\Tabs\EditController::class,
        'capture' => \App\Http\Controllers\Device\Tabs\CaptureController::class,
    ];

=======
>>>>>>> upstream/master
    public function index(Request $request, $device, $current_tab = 'overview', $vars = '')
    {
        $device = str_replace('device=', '', $device);
        $device = is_numeric($device) ? DeviceCache::get((int) $device) : DeviceCache::getByHostname($device);
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
}
