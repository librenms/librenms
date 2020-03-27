<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Vminfo;
use Carbon\Carbon;
use DB;
use LibreNMS\Config;

class DeviceController extends Controller
{
    private $tabs = [
        'overview' => \App\Http\Controllers\Device\Tabs\OverviewController::class,
        'graphs' => \App\Http\Controllers\Device\Tabs\GraphsController::class,
        'health' => \App\Http\Controllers\Device\Tabs\HealthController::class,
        'apps' => \App\Http\Controllers\Device\Tabs\AppsController::class,
        'processes' => \App\Http\Controllers\Device\Tabs\ProcessesController::class,
        'collectd' => \App\Http\Controllers\Device\Tabs\CollectdController::class,
        'ports' => \App\Http\Controllers\Device\Tabs\PortsController::class,
        'slas' => \App\Http\Controllers\Device\Tabs\SlasController::class,
        'wireless' => \App\Http\Controllers\Device\Tabs\WirelessController::class,
        'accesspoints' => \App\Http\Controllers\Device\Tabs\AccessPointsController::class,
        'latency' => \App\Http\Controllers\Device\Tabs\LatencyController::class,
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
        'performance' => \App\Http\Controllers\Device\Tabs\PerformanceController::class,
        'nac' => \App\Http\Controllers\Device\Tabs\NacController::class,
        'notes' => \App\Http\Controllers\Device\Tabs\NotesController::class,
        'mib' => \App\Http\Controllers\Device\Tabs\MibController::class,
    ];

    public function index($device_id, $current_tab)
    {
        $current_tab = array_key_exists($current_tab, $this->tabs) ? $current_tab : 'overview';
        $device_id = (int)str_replace('device=', '', $device_id);
        DeviceCache::setPrimary($device_id);
        $device = DeviceCache::getPrimary();

        $alert_class = $device->disabled ? 'alert-info' : ($device->status ? '' : 'alert-danger');
        $parent_id = Vminfo::query()->whereIn('vmwVmDisplayName', [$device->hostname, $device->hostname . '.' . Config::get('mydomain')])->first(['device_id']);
        $overview_graphs = $this->buildDeviceGraphArrays($device);

        $tabs = array_map(function ($class) {
            return app()->make($class);
        }, array_filter($this->tabs, 'class_exists')); // TODO remove filter
        $title = $tabs[$current_tab]->name();
        $data = $tabs[$current_tab]->data($device);

        if (view()->exists('device.tabs.' . $current_tab)) {
            return view('device.tabs.' . $current_tab, get_defined_vars());
        }

        $tab_content = $this->renderLegacyTab($current_tab, $device, $data);
        return view('device.tabs.legacy', get_defined_vars());
    }

    private function renderLegacyTab($tab, Device $device, $data)
    {
        ob_start();
        $device = $device->toArray();
        set_debug(false);
        chdir(base_path());
        include 'includes/common.php';
        include 'includes/functions.php';
        include 'includes/dbFacile.php';
        include 'includes/rrdtool.inc.php';
        include 'includes/rewrites.php';
        include 'includes/html/functions.inc.php';
        extract($data); // set preloaded data into variables
        include "includes/html/pages/device/$tab.inc.php";
        $output = ob_get_clean();
        ob_end_clean();

        return $output;
    }

    private function buildDeviceGraphArrays($device)
    {
        $graph_array = [
            'width' => 150,
            'height' => 45,
            'device' => $device->device_id,
            'type' => 'device_bits',
            'from' => Carbon::now()->subDay()->timestamp,
            'legend' => 'no',
            'bg' => 'FFFFFF00',
        ];

        $graphs = [];
        foreach ($this->getDeviceGraphs($device) as $graph) {
            $graph_array['type'] = $graph['graph'];
            $graph_array['popup_title'] = __($graph['text']);
            $graphs[] = $graph_array;
        }

        return $graphs;
    }

    private function getDeviceGraphs(Device $device)
    {
        if ($device->snmp_disable) {
            return Config::get('os.ping.over');
        } elseif (Config::has("os.$device->os.over")) {
            return Config::get("os.$device->os.over");
        }

        $os_group = Config::getOsSetting($device->os, 'group');
        return Config::get("os.$os_group.over", Config::get('os.default.over'));
    }
}
