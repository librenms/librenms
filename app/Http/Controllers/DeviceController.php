<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Vminfo;
use Carbon\Carbon;
use DB;
use \LibreNMS\Config;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index($device_id, $tab = 'overview') {
        $device_id = (int)str_replace('device=', '', $device_id);
        DeviceCache::setPrimary($device_id);
        $device = DeviceCache::getPrimary();

        $alert_class = $device->disabled ? 'alert-info' : ($device->status ? '' : 'alert-danger');
        $parent_id = Vminfo::query()->whereIn('vmwVmDisplayName', [$device->hostname, $device->hostname . '.' . Config::get('mydomain')])->first(['device_id']);
        $overview_graphs = $this->buildDeviceGraphArrays($device);

        $show_health_tab = $device->storage()->exists() || $device->sensors()->exists() || $device->mempools()->exists() || $device->processors()->exists();
        $show_apps_tab = $device->applications()->exists();
        $show_processes_tab = DB::table('processes')->where('device_id', $device_id)->exists();
        $show_collectd_tab = Config::has('collectd_dir') && is_dir(Config::get('collectd_dir') . '/' . $device->hostname . '/');
        $show_munin_tab = $device->muninPlugins()->exists();
        $show_ports_tab = $device->ports()->exists();

        $tab_content = $this->renderLegacyTab($tab, $device);

        return view('device.' . $tab, get_defined_vars());
    }

    private function renderLegacyTab($tab, Device $device)
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
