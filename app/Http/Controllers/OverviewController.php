<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\UserPref;
use App\Models\UserWidget;
use App\Models\Dashboard;
use App\Models\Widget;
use Auth;
use LibreNMS\Config;
use Toastr;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
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
        list($user_dashboards, $shared_dashboards) = $dashboards->partition(function ($dashboard) use ($user) {
            return $dashboard->user_id == $user->user_id;
        });



        if (!empty($request->dashboard) && isset($dashboards[$request->dashboard])) {
            // specific dashboard
            $dashboard = $dashboards[$request->dashboard];
        } else {
            $user_default_dash = (int)UserPref::getPref($user, 'dashboard');
            $global_default = (int)Config::get('webui.default_dashboard_id');

            // load user default
            if (isset($dashboards[$user_default_dash])) {
                $dashboard = $dashboards[$user_default_dash];
            // load global default
            } elseif (isset($dashboards[$global_default])) {
                $dashboard = $dashboards[$global_default];
            // load users first dashboard
            } elseif (!empty($user_dashboards)) {
                $dashboard = $user_dashboards->first();
            }

            // specific dashboard was requested, but doesn't exist
            if (isset($dashboard) && !empty($request->dashboard)) {
                Toastr::error(
                    "Dashboard <code>#$request->dashboard</code> does not exist! Loaded <code>
                    ".htmlentities($dashboard->dashboard_name)."</code> instead.",
                    "Requested Dashboard Not Found!"
                );
            }
        }

        if (!isset($dashboard)) {
            $dashboard = Dashboard::create([
                'dashboard_name' => 'Default',
                'user_id' => $user->user_id,
            ]);

            // TODO: Is this still needed?
            UserWidget::where('user_id', $user->user_id)
              ->where('dashboard_id', 0)
              ->update(['dashboard_id' => $dashboard->dashboard_id]);
        }

        $data = $dashboard
            ->widgets()
            ->select(['user_widget_id','users_widgets.widget_id','title','widget','col','row','size_x','size_y','refresh'])
            ->join('widgets', 'widgets.widget_id', '=', 'users_widgets.widget_id')
            ->get();

        if ($data->isEmpty()) {
            $data[] = array('user_widget_id'=>'0',
                            'widget_id'=>1,
                            'title'=>'Add a widget',
                            'widget'=>'placeholder',
                            'col'=>1,
                            'row'=>1,
                            'size_x'=>6,
                            'size_y'=>2,
                            'refresh'=>60
                        );
        }

        $bare        = $request->bare;
        $data        = serialize(json_encode($data));
        $dash_config = unserialize(stripslashes($data));
        $widgets     = Widget::select('widget_id', 'widget_title')->orderBy('widget_title')->get();

        return view('overview.default', compact('bare', 'dash_config', 'dashboard', 'user_dashboards', 'shared_dashboards', 'widgets'));
    }
}
