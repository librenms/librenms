<?php

namespace App\Http\Controllers;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Vminfo;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Graph;
use LibreNMS\Util\Url;

class DeviceController extends Controller
{
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
        'latency' => \App\Http\Controllers\Device\Tabs\LatencyController::class,
        'nac' => \App\Http\Controllers\Device\Tabs\NacController::class,
        'notes' => \App\Http\Controllers\Device\Tabs\NotesController::class,
        'edit' => \App\Http\Controllers\Device\Tabs\EditController::class,
        'capture' => \App\Http\Controllers\Device\Tabs\CaptureController::class,
    ];

    public function index(Request $request, $device, $current_tab = 'overview', $vars = '')
    {
        $device = str_replace('device=', '', $device);
        $device = is_numeric($device) ? DeviceCache::get((int) $device) : DeviceCache::getByHostname($device);
        $device_id = $device->device_id;

        if (! $device->exists) {
            abort(404);
        }

        DeviceCache::setPrimary($device_id);

        $current_tab = str_replace('tab=', '', $current_tab);
        $current_tab = array_key_exists($current_tab, $this->tabs) ? $current_tab : 'overview';

        if ($current_tab == 'port') {
            $vars = Url::parseLegacyPath($request->path());
            $port = $device->ports()->findOrFail($vars->get('port'));
            $this->authorize('view', $port);
        } else {
            $this->authorize('view', $device);
        }

        $alert_class = $device->disabled ? 'alert-info' : ($device->status ? '' : 'alert-danger');
        $parent_id = Vminfo::guessFromDevice($device)->value('device_id');
        $overview_graphs = $this->buildDeviceGraphArrays($device);

        /** @var DeviceTab[] $tabs */
        $tabs = array_map(function ($class) {
            return app()->make($class);
        }, array_filter($this->tabs, 'class_exists')); // TODO remove filter
        $tab_controller = $tabs[$current_tab];
        $title = $tab_controller->name();
        $data = $tab_controller->data($device, $request);
        $page_links = $data['page_links'] ?? [];

        // Device Link Menu, select the primary link
        $device_links = $this->deviceLinkMenu($device, $current_tab);
        $primary_device_link_name = Config::get('html.device.primary_link', 'edit');
        if (! isset($device_links[$primary_device_link_name])) {
            $primary_device_link_name = array_key_first($device_links);
        }
        $primary_device_link = $device_links[$primary_device_link_name];
        unset($device_links[$primary_device_link_name], $primary_device_link_name);

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
        $device['os_group'] = Config::get("os.{$device['os']}.group");
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
        foreach (Graph::getOverviewGraphsForDevice($device) as $graph) {
            $graph_array['type'] = $graph['graph'];
            $graph_array['popup_title'] = __($graph['text']);
            $graphs[] = $graph_array;
        }

        return $graphs;
    }

    private function deviceLinkMenu(Device $device, string $current_tab): array
    {
        $device_links = [];

        if (Gate::allows('update', $device)) {
            $suffix = 'edit';
            $title = __('Edit');

            // check if metric has more specific edit page
            $path = \Request::path();
            if (preg_match('#health/metric=(\w+)#', $path, $matches)) {
                if ($this->editTabExists($matches[1])) {
                    $current_tab = $matches[1];
                } elseif ($this->editTabExists($matches[1] . 's')) {
                    $current_tab = $matches[1] . 's';
                }
            } elseif (preg_match('#device/\d+/ports/transceivers#', $path)) {
                $current_tab = 'transceivers';
            }

            // check if edit page exists
            if ($this->editTabExists($current_tab)) {
                $suffix .= "/section=$current_tab";
                $title .= ' ' . __(ucfirst($current_tab));
            }

            $device_links['edit'] = [
                'icon' => 'fa-gear',
                'url' => route('device', [$device->device_id, $suffix]),
                'title' => $title,
                'external' => false,
            ];
        }

        // User defined device links
        foreach (array_values(Arr::wrap(Config::get('html.device.links'))) as $index => $link) {
            $device_links['custom' . ($index + 1)] = [
                'icon' => $link['icon'] ?? 'fa-external-link',
                'url' => Blade::render($link['url'], ['device' => $device]),
                'title' => $link['title'],
                'external' => $link['external'] ?? true,
            ];
        }

        // Web
        $http_port = $device->attribs->firstWhere('attrib_type', 'override_device_http_port') ? ':' . $device->attribs->firstWhere('attrib_type', 'override_device_http_port')->attrib_value : '';
        $device_links['web'] = [
            'icon' => 'fa-globe',
            'url' => 'https://' . $device->hostname . $http_port,
            'title' => __('Web'),
            'external' => true,
            'onclick' => 'http_fallback(this); return false;',
        ];

        // IPMI
        if ($device->attribs->firstWhere('attrib_type', 'ipmi_hostname')) {
            $device_links['ipmi'] = [
                'icon' => 'fa-microchip',
                'url' => 'https://' . $device->attribs->firstWhere('attrib_type', 'ipmi_hostname')->attrib_value,
                'title' => __('IPMI'),
                'external' => true,
                'onclick' => 'http_fallback(this); return false;',
            ];
        }

        // SSH
        $ssh_port = $device->attribs->firstWhere('attrib_type', 'override_device_ssh_port') ? ':' . $device->attribs->firstWhere('attrib_type', 'override_device_ssh_port')->attrib_value : '';
        $ssh_url = Config::has('gateone.server')
            ? Config::get('gateone.server') . '?ssh=ssh://' . (Config::get('gateone.use_librenms_user') ? Auth::user()->username . '@' : '') . $device['hostname'] . '&location=' . $device['hostname']
            : 'ssh://' . $device->hostname . $ssh_port;
        $device_links['ssh'] = [
            'icon' => 'fa-lock',
            'url' => $ssh_url,
            'title' => __('SSH'),
            'external' => true,
        ];

        // Telnet
        $telnet_port = $device->attribs->firstWhere('attrib_type', 'override_device_telnet_port') ? ':' . $device->attribs->firstWhere('attrib_type', 'override_device_telnet_port')->attrib_value : '';
        $device_links['telnet'] = [
            'icon' => 'fa-terminal',
            'url' => 'telnet://' . $device->hostname . $telnet_port,
            'title' => __('Telnet'),
            'external' => true,
        ];

        if (Gate::allows('admin')) {
            $device_links['capture'] = [
                'icon' => 'fa-bug',
                'url' => route('device', [$device->device_id, 'capture']),
                'title' => __('Capture'),
                'external' => false,
            ];
        }

        return $device_links;
    }

    private function editTabExists(string $tab): bool
    {
        return is_file(base_path("includes/html/pages/device/edit/$tab.inc.php"));
    }
}
