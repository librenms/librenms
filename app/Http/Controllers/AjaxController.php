<?php

namespace App\Http\Controllers;

use App\Http\Forms\BaseForm;
use App\Http\Selects\BaseSelect;
use App\Models\AlertRule;
use App\Models\Application;
use App\Models\BgpPeer;
use App\Models\Bill;
use App\Models\Device;
use App\Models\DeviceGroup;
use App\Models\MuninPlugin;
use App\Models\Port;
use App\Models\User;
use App\Models\UsersWidgets;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use LibreNMS\Config;

class AjaxController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth');
        session_start();
        session_write_close();
        Auth::onceUsingId(\LibreNMS\Authentication\Auth::id());

        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }
    }

    public function setResolution(Request $request)
    {
        $this->validate($request, [
            'width' => 'required|numeric',
            'height' => 'required|numeric'
        ]);

        // legacy session
        session_start();
        $_SESSION['screen_width'] = $request->width;
        $_SESSION['screen_height'] = $request->height;
        session_write_close();

        // laravel session
        session([
            'screen_width' => $request->width,
            'screen_height' => $request->height
        ]);

        return $request->width . 'x' . $request->height;
    }

    public function dash(Request $request)
    {
        global $config;
        set_debug($request->get('debug', false)); // hide old code errors
        session_start();
        session_write_close();

        $type = $request->get('type');
        $status = 'error';
        $output = '';
        $title = '';

        $filename = base_path("html/includes/common/$type.inc.php");

        if ($type == 'placeholder') {
            $output = "<span style='text-align:left;'><br><h3>Click on the Edit Dashboard button (next to the list of dashboards) to add widgets</h3><br><h4><strong>Remember:</strong> You can only move & resize widgets when you're in <strong>Edit Mode</strong>.</h4><span>";
            $status = 'ok';
            $title = 'Placeholder';
        } elseif (file_exists($filename)) {
            $common_output = [];
            $results_limit = 10;
            $typeahead_limit = Config::get('webui.global_search_result_limit', 8);
            $no_form = true;
            $unique_id = str_replace(array("-", "."), "_", uniqid($type, true));
            $widget_id = $request->get('id');
            $widget_settings = UsersWidgets::select('settings')->find($widget_id)->settings ?: [];
            $widget_dimensions = $request->get('dimensions');
            if ($request->get('settings')) {
                define('SHOW_SETTINGS', true);
            }

            include $filename;
            $output = implode('', $common_output);
            $status = 'ok';
            $title = strip_tags($widget_settings['title'] ?: ucfirst($type));
        }

        return response()->json([
            'status' => $status,
            'html' => $output,
            'title' => $title,
        ]);
    }

    public function table(Request $request)
    {
        $this->validate($request, [
            'current' => 'integer',
            'rowCount' => 'integer',
            'sort' => 'array',
            'id' => ['required', 'regex:/^[a-zA-Z0-9\-]+$/'],
        ]);

        global $config;
        set_debug($request->get('debug', false)); // hide old code errors
        session_start();
        session_write_close();

        $current = $request->get('current');
        $rowCount = $request->get('rowCount');
        $sort = '';
        foreach ($request->get('sort', []) as $k => $v) {
            $sort .= " $k $v";
        }

        $searchPhrase = $request->get('searchPhrase');
        $id = $request->get('id');
        $filename = base_path("html/includes/table/$id.inc.php");

        $response = [];
        $total = 0;
        if (file_exists($filename)) {
            include $filename;
        }

        return response()->json([
            'current' => $current,
            'rowCount' => $rowCount,
            'rows' => $response,
            'total' => $total,
        ]);
    }

    public function form(Request $request)
    {
        $this->validate($request, [
            'type' => ['required', 'regex:/^[a-zA-Z0-9\-]+$/']
        ]);

        $type = $request->get('type');
        $class = str_to_class($type, 'App\\Http\\Forms\\');

        if (class_exists($class)) {
            /** @var BaseForm $form */
            $form = new $class;
            $this->validate($request, $form->validationRules(), $form->validationMessages());

            $response = $form->handleRequest($request);

            return response()->json($response);
        } else {
            $filename = base_path("html/includes/forms/$type.inc.php");
            if (file_exists($filename)) {
                global $config;
                set_debug($request->get('debug', false)); // hide old code errors
                session_start();
                session_write_close();

                include $filename;

                return null;
            }
        }

        return response('Form type not found', 404);
    }

    /**
     * Lists for select2
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function select(Request $request)
    {
        $this->validate($request, [
            'type' => ['required', 'regex:/^[a-zA-Z0-9\-]+$/'],
            'limit' => 'integer',
            'page' => 'integer',
        ]);

        $type = $request->get('type');
        $class = str_to_class($type, 'App\\Http\\Selects\\');

        if (class_exists($class)) {
            /** @var BaseSelect $form */
            $form = new $class($request);
            $response = $form->get();

            return response()->json($response);
        }

        return response('List type not found', 404);
    }

    public function listPorts(Request $request)
    {
        // TODO port to select2
        $this->validate($request, [
            'device_id' => 'required|integer'
        ]);

        $output = Port::where('device_id', $request->get('device_id'))->get()
            ->reduce(function ($output, $port) {
                $string = addslashes(html_entity_decode($port->getLabel() . ' - ' . $port->ifAlias));
                return $output . "obj.options[obj.options.length] = new Option('$string','{$port->port_id}');\n";
            }, '');

        return response($output);
    }

    public function ruleSuggest(Request $request)
    {
        $this->validate($request, [
            'term' => ['required', 'regex:/^[a-zA-Z0-9\-_]+$/']
        ]);

        $term = $request->get('term');

        if (str_contains($term, '.')) {
            list($prefix,) = explode('.', $term);
            if ($prefix == 'macros') {
                $list = collect(Config::get('alert.macros.rule'))
                    ->map(function ($value, $key) {
                        return 'macros.' . $key;
                    })->values();
            } else {
                $list = collect(DB::getSchemaBuilder()
                    ->getColumnListing($prefix))
                    ->map(function ($field) use ($prefix) {
                        return $prefix . '.' . $field;
                    });
            }
        } else {
            $list = $tables = DB::table('INFORMATION_SCHEMA.COLUMNS')
                ->select('TABLE_NAME')
                ->where('COLUMN_NAME', 'device_id')
                ->get()
                ->map(function ($data) {
                    return $data->TABLE_NAME . '.';
                });

            $list->push('macros.');
            $list->push('bills.');
        }

        $list = $list
            ->sort(function ($a, $b) use ($term) {
                // sort by string similarity
                $levA = levenshtein($term, $a);
                $levB = levenshtein($term, $b);

                return $levA === $levB ? 0 : ($levA > $levB ? 1 : -1);
            })
            ->take(20)
            ->map(function ($value) {
                return ['name' => $value];
            });

        if ($list->isEmpty()) {
            $list = [['name' => 'Error: No suggestions found.']];
        }

        return response()->json($list);
    }

    public function osSuggest(Request $request)
    {
        $this->validate($request, [
            'term' => ['required', 'regex:/^[a-zA-Z0-9\-_]+$/'],
            'limit' => 'integer',
            'page' => 'integer',
        ]);

        $term = strtolower($request->get('term'));

        load_all_os();

        $ret = collect(Config::get('os'))->sort(function ($a, $b) use ($term){
            // sort by string similarity
            $levA = levenshtein($term, strtolower($a['os'] . $a['text']), 1, 10, 10);
            $levB = levenshtein($term, strtolower($b['os'] . $b['text']), 1, 10, 10);

            return $levA === $levB ? 0 : ($levA > $levB ? 1 : -1);
        })->take(20)->map(function ($os) {
            return ['os' => $os['os'], 'text' => $os['text']];
        });

        if ($ret->isEmpty()) {
            $ret = [['Error: No suggestions found.']];
        }

        return response()->json($ret);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'search' => ['required', 'regex:/^[a-zA-Z0-9\-_]+$/'],
            'map' => 'boolean|integer',
        ]);

        $user = $request->user();
        $limit = Config::get('webui.global_search_result_limit', 8);

        $type = $request->get('type');
        $map = $request->get('map', false);
        $search = '%' . $request->get('search') . '%';

        if ($type == 'group') {
            $groups = DeviceGroup::select(['id', 'name'])
                ->hasAccess($user)
                ->where('name', 'like', $search)
                ->limit($limit)
                ->get()
                ->map(function ($group) use ($map) {
                    if ($map) {
                        return [
                            'name' => 'g:' . $group->name,
                            'group_id' => $group->id,
                        ];
                    } else {
                        return ['name' => $group->name];
                    }
                });

            return response()->json($groups);
        }

        if ($type == 'alert-rules') {
            $rules = AlertRule::select('name')
                ->hasAccess($user)
                ->where('name', 'like', $search)
                ->limit($limit)
                ->get();

            return response()->json($rules);
        }

        if ($type == 'device') {
            $devices = Device::hasAccess($user)
                ->where(function ($query) use ($search) {
                    $query->where('hostname', 'like', $search)
                        ->orWhere('sysName', 'like', $search)
                        ->orWhere('location', 'like', $search)
                        ->orWhere('purpose', 'like', $search)
                        ->orWhere('notes', 'like', $search);
                })
            ->orderBy('hostname')
            ->limit($limit)
            ->get()
            ->map(function ($device) use ($request, $map) {
                /** @var Device $device */
                $name = $device->hostname;
                if (!$map && !empty($device->sysName) && $device->sysName != $device->hostname) {
                    $name .= " ($device->sysName) ";
                }

                return [
                    'name' => $name,
                    'device_id' => $device->device_id,
                    'url' => generate_device_url($device->toArray()),
                    'device_ports' => $device->ports()->count(),
                    'device_image' => $device->icon,
                    'device_hardware' => $device->hardware,
                    'device_os' => Config::get("os.{$device->os}.text"),
                    'version' => $device->version,
                    'location' => $device->location,
                ];
            });

            return response()->json($devices);
        }

        if ($type == 'ports') {
            $ports = Port::select(['port_id', 'ifIndex', 'ifName', 'ifAlias', 'ifDescr'])
                ->hasAccess($user)
                ->where(function ($query) use ($search) {
                    $query->where('ifAlias', 'like', $search)
                        ->orWhere('ifDescr', 'like', $search)
                        ->orWhere('ifName', 'like', $search);
                })
            ->orderBy('ifDescr')
            ->limit($limit)
            ->get()
            ->map(function ($port) {
                /** @var Port $port */
                return [
                    'url' => generate_port_url($port->toArray()),
                    'name' => $port->getLabel(),
                    'description' => $port->ifAlias,
                    'hostname' => $port->device->hostname,
                    'port_id' => $port->port_id,
                ];
            });

            return response()->json($ports);
        }

        if ($type == 'bgp') {
            $bgp_peers = BgpPeer::hasAccess($user)
                ->where(function ($query) use ($search) {
                    $query->where('astext', 'like', $search)
                        ->orWhere('bgpPeerIdentifier', 'like', $search)
                        ->orWhere('bgpPeerRemoteAs', 'like', $search);
                })
                ->orderBy('astext')
                ->limit($limit)
                ->get()
                ->map(function ($peer) {
                    if ($peer->bgpPeerRemoteAs == $peer->bgpLocalAs) {
                        $bgp_image = '<i class="fa fa-square fa-lg icon-theme" aria-hidden="true"></i>';
                    } else {
                        $bgp_image = '<i class="fa fa-external-link-square fa-lg icon-theme" aria-hidden="true"></i>';
                    }

                    return [
                        'url' => generate_peer_url($peer->toArray()),
                        'name' => $peer->bgpPeerIdentifier,
                        'description' => $peer->astext,
                        'localas' => $peer->bgpLocalAs,
                        'bgp_image' => $bgp_image,
                        'remoteas' => $peer->bgpPeerRemoteAs,
                        'hostname' => $peer->device->hostname,
                    ];
                });

            return response()->json($bgp_peers);
        }

        if ($type == 'applications') {
            $apps = Application::hasAccess($user)
                ->with('device')
                ->where(function ($query) use ($search) {
                    $query->where('app_type', 'like', $search)
                        ->orWhere('devices.hostname', 'like', $search);
                })
                ->orderBy('devices.hostname')
                ->limit($limit)
                ->map(function ($app) {
                    return [
                        'name' => $app->app_type,
                        'hostname' => $app->device->hostname,
                        'app_id' => $app->app_id,
                        'device_id' => $app->device_id,
                        'device_image' => $app->device->icon,
                        'device_hardware' => $app->device->hardware,
                        'device_os' => Config::get("os.{$app->device->os}.text"),
                        'version' => $app->device->version,
                        'location' => $app->device->location,
                    ];
                });

            return response()->json($apps);
        }

        if ($type == 'iftype') {
            $iftypes = Port::select('ifType')
                ->hasAccess($user)
                ->where('ifType', 'like', $search)
                ->groupBy('ifType')
                ->orderBy('ifType')
                ->limit($limit)
                ->get()
                ->map(function ($port) {
                    return ['filter' => $port->ifType];
                });

            return response()->json($iftypes);
        }

        if ($type == 'bill') {
            $bills = Bill::select(['bill_id', 'bill_name'])
                ->hasAccess($user)
                ->where(function ($query) use ($search) {
                    $query->where('bill_name', 'like', $search)
                        ->orWhere('bill_notes', 'like', $search);
                })
                ->orderBy('bill_name')
                ->limit($limit)
                ->get();

            return response()->json($bills);
        }

        if ($type == 'munin') {
            $munin_plugins = MuninPlugin::hasAccess($user)
                ->leftJoin('devices', 'munin_plugins.device_id', 'devices.device_id')
                ->with('device')
                ->where(function ($query) use ($search) {
                    $query->where('mplug_type', 'like', $search)
                        ->orWhere('mplug_title', 'like', $search)
                        ->orWhere('devices.hostname', 'like', $search);
                })
                ->orderBy('devices.hostname')
                ->limit($limit)
                ->get()
                ->map(function ($plugin) {
                    return [
                        'name' => $plugin->mplug_title,
                        'hostname' => $plugin->device->hostname,
                        'device_id' => $plugin->device_id,
                        'device_image' => $plugin->device->icon,
                        'device_hardware' => $plugin->device->hardware,
                        'device_os' => Config::get("os.{$plugin->device->os}.text"),
                        'version' => $plugin->device->version,
                        'location' => $plugin->device->location,
                        'plugin' => $plugin->mplug_type,
                    ];
                });

            return response()->json($munin_plugins);
        }

        return response()->json([]);
    }
}
