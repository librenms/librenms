<?php

namespace App\Http\Controllers;

use App\Models\BgpPeer;
use App\Models\Device;
use App\Models\Port;
use App\Models\Service;
use App\Models\Syslog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;

class OverviewController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $view = Config::get('front_page');

        if (view()->exists("overview.custom.$view")) {
            return view("overview.custom.$view");
        } elseif (method_exists($this, $view)) {
            return $this->{$view}($request);
        }

        // default to dashboard
        return (new DashboardController())->index($request);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
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
        $bgp_down = BgpPeer::hasAccess(Auth::user())
            ->inAlarm()
            ->limit(Config::get('front_page_down_box_limit'))
            ->with('device')
            ->get();

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
