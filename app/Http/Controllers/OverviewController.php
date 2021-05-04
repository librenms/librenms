<?php

namespace App\Http\Controllers;

use App\Models\BgpPeer;
use App\Models\Dashboard;
use App\Models\Device;
use App\Models\Port;
use App\Models\Service;
use App\Models\Syslog;
use App\Models\User;
use App\Models\UserPref;
use App\Models\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;
use Toastr;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'dashboard' => 'integer',
            'bare' => 'nullable|in:yes',
        ]);

        $view = Config::get('front_page');

        if (view()->exists("overview.custom.$view")) {
            return view("overview.custom.$view");
        } elseif (method_exists($this, $view)) {
            return $this->{$view}($request);
        }

        return $this->default($request);
    }

    public function default(Request $request)
    {
        $user = Auth::user();
        $dashboards = Dashboard::allAvailable($user)->with('user:user_id,username')->get()->keyBy('dashboard_id');

        // Split dashboards into user owned or shared
        [$user_dashboards, $shared_dashboards] = $dashboards->partition(function ($dashboard) use ($user) {
            return $dashboard->user_id == $user->user_id;
        });

        if (! empty($request->dashboard) && isset($dashboards[$request->dashboard])) {
            // specific dashboard
            $dashboard = $dashboards[$request->dashboard];
        } else {
            $user_default_dash = (int) UserPref::getPref($user, 'dashboard');
            $global_default = (int) Config::get('webui.default_dashboard_id');

            // load user default
            if (isset($dashboards[$user_default_dash])) {
                $dashboard = $dashboards[$user_default_dash];
            // load global default
            } elseif (isset($dashboards[$global_default])) {
                $dashboard = $dashboards[$global_default];
            // load users first dashboard
            } elseif (! empty($user_dashboards)) {
                $dashboard = $user_dashboards->first();
            }

            // specific dashboard was requested, but doesn't exist
            if (isset($dashboard) && ! empty($request->dashboard)) {
                Toastr::error(
                    "Dashboard <code>#$request->dashboard</code> does not exist! Loaded <code>
                    " . htmlentities($dashboard->dashboard_name) . '</code> instead.',
                    'Requested Dashboard Not Found!'
                );
            }
        }

        if (! isset($dashboard)) {
            $dashboard = Dashboard::create([
                'dashboard_name' => 'Default',
                'user_id' => $user->user_id,
            ]);
        }

        $data = $dashboard
            ->widgets()
            ->select(['user_widget_id', 'users_widgets.widget_id', 'title', 'widget', 'col', 'row', 'size_x', 'size_y', 'refresh', 'settings'])
            ->join('widgets', 'widgets.widget_id', '=', 'users_widgets.widget_id')
            ->get();

        if ($data->isEmpty()) {
            $data[] = ['user_widget_id'=>'0',
                'widget_id'=>1,
                'title'=>'Add a widget',
                'widget'=>'placeholder',
                'col'=>1,
                'row'=>1,
                'size_x'=>6,
                'size_y'=>2,
                'refresh'=>60,
            ];
        }

        $bare = $request->bare;
        $data = serialize(json_encode($data));
        $dash_config = unserialize($data);
        $hide_dashboard_editor = UserPref::getPref($user, 'hide_dashboard_editor');
        $widgets = Widget::select('widget_id', 'widget_title')->orderBy('widget_title')->get();

        $user_list = [];
        if ($user->can('manage', User::class)) {
            $user_list = User::select(['username', 'user_id'])
                ->where('user_id', '!=', $user->user_id)
                ->orderBy('username')
                ->get();
        }

        return view('overview.default', compact('bare', 'dash_config', 'dashboard', 'hide_dashboard_editor', 'user_dashboards', 'shared_dashboards', 'widgets', 'user_list'));
    }

    public function simple(Request $request)
    {
        //TODO: All below missing D.ignore = '0' check
        $ports_down = [];
        $bgp_down = [];
        $devices_uptime = [];
        $syslog = [];

        $devices_down = Device::hasAccess(Auth::user())
            ->isDown()
            ->limit(Config::get('front_page_down_box_limit'))
            ->get();

        if (Config::get('warn.ifdown')) {
            $ports_down = Port::hasAccess(Auth::user())
                ->isDown()
                ->limit(Config::get('front_page_down_box_limit'))
                ->with('device')
                ->get();
        }

        $services_down = Service::hasAccess(Auth::user())
            ->isCritical()
            ->limit(Config::get('front_page_down_box_limit'))
            ->with('device')
            ->get();

        // TODO: is inAlarm() equal to: bgpPeerAdminStatus != 'start' AND bgpPeerState != 'established' AND bgpPeerState != ''  ?
        if (Config::get('enable_bgp')) {
            $bgp_down = BgpPeer::hasAccess(Auth::user())
                ->inAlarm()
                ->limit(Config::get('front_page_down_box_limit'))
                ->with('device')
                ->get();
        }

        if (filter_var(Config::get('uptime_warning'), FILTER_VALIDATE_FLOAT) !== false
            && Config::get('uptime_warning') > 0
        ) {
            $devices_uptime = Device::hasAccess(Auth::user())
                ->isUp()
                ->whereUptime(Config::get('uptime_warning'))
                ->limit(Config::get('front_page_down_box_limit'))
                ->get();

            $devices_uptime = $devices_uptime->reject(function ($device) {
                return Config::getOsSetting($device->os, 'bad_uptime') == true;
            });
        }

        if (Config::get('enable_syslog')) {
            $syslog = Syslog::hasAccess(Auth::user())
            ->orderBy('timestamp', 'desc')
            ->limit(20)
            ->with('device')
            ->get();
        }

        return view('overview.simple', compact('devices_down', 'ports_down', 'services_down', 'bgp_down', 'devices_uptime', 'syslog'));
    }
}
