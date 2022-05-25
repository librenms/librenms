<?php
/**
 * DashboardController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App\Models\Dashboard;
use App\Models\User;
use App\Models\UserPref;
use App\Models\UserWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use LibreNMS\Config;

class DashboardController extends Controller
{
    /** @var string[] */
    public static $widgets = [
        'alerts',
        'alertlog',
        'alertlog-stats',
        'availability-map',
        'component-status',
        'device-summary-horiz',
        'device-summary-vert',
        'device-types',
        'eventlog',
        'globe',
        'generic-graph',
        'graylog',
        'generic-image',
        'notes',
        'server-stats',
        'syslog',
        'top-devices',
        'top-errors',
        'top-interfaces',
        'worldmap',
    ];

    /** @var \Illuminate\Support\Collection<\App\Models\Dashboard> */
    private $dashboards;

    public function __construct()
    {
        $this->authorizeResource(Dashboard::class, 'dashboard');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $request->validate([
            'dashboard' => 'integer',
            'bare' => 'nullable|in:yes',
        ]);

        $user = $request->user();
        $dashboards = $this->getAvailableDashboards($user);

        // specific dashboard
        if (! empty($request->dashboard) && $dashboards->has($request->dashboard)) {
            return $this->show($request, $dashboards->get($request->dashboard));
        }

        // default dashboard
        $user_default_dash = (int) UserPref::getPref($user, 'dashboard');
        $global_default = (int) Config::get('webui.default_dashboard_id');

        // load user default
        if ($dashboards->has($user_default_dash)) {
            return $this->show($request, $dashboards->get($user_default_dash));
        }

        // load global default
        if ($dashboards->has($global_default)) {
            return $this->show($request, $dashboards->get($global_default));
        }

        // load users first dashboard
        $user_first_dashboard = $dashboards->firstWhere('user_id', $user->user_id);
        if ($user_first_dashboard) {
            return $this->show($request, $user_first_dashboard);
        }

        // create a dashboard for this user
        return $this->show($request, Dashboard::create([
            'dashboard_name' => 'Default',
            'user_id' => $user->user_id,
        ]));
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Dashboard  $dashboard
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Request $request, Dashboard $dashboard)
    {
        $request->validate([
            'bare' => 'nullable|in:yes',
        ]);

        $user = Auth::user();

        // Split dashboards into user owned or shared
        $dashboards = $this->getAvailableDashboards($user);
        [$user_dashboards, $shared_dashboards] = $dashboards->partition(function ($dashboard) use ($user) {
            return $dashboard->user_id == $user->user_id;
        });

        $data = $dashboard->widgets;

        if ($data->isEmpty()) {
            $data = [
                [
                    'user_widget_id' => 0,
                    'title' => 'Add a widget',
                    'widget' => 'placeholder',
                    'col' => 1,
                    'row' => 1,
                    'size_x' => 6,
                    'size_y' => 2,
                    'refresh' => 60,
                ],
            ];
        }

        $widgets = array_combine(self::$widgets, array_map(function ($widget) {
            return trans("widgets.$widget.title");
        }, self::$widgets));

        $user_list = $user->can('manage', User::class)
            ? User::where('user_id', '!=', $user->user_id)
                ->orderBy('username')
                ->pluck('username', 'user_id')
            : [];

        return view('overview.default', [
            'bare' => $request->get('bare'),
            'dash_config' => $data,
            'dashboard' => $dashboard,
            'hide_dashboard_editor' => UserPref::getPref($user, 'hide_dashboard_editor'),
            'user_dashboards' => $user_dashboards,
            'shared_dashboards' => $shared_dashboards,
            'widgets' => $widgets,
            'user_list' => $user_list,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'dashboard_name' => 'string|max:255',
        ]);

        $name = trim(strip_tags($request->get('dashboard_name')));
        $dashboard = Dashboard::create([
            'user_id' => Auth::id(),
            'dashboard_name' => $name,
            'access' => 0,
        ]);

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'Dashboard ' . htmlentities($name) . ' created',
            'dashboard_id' => $dashboard->dashboard_id,
        ]);
    }

    public function update(Request $request, Dashboard $dashboard): JsonResponse
    {
        $validated = $this->validate($request, [
            'dashboard_name' => 'string|max:255',
            'access' => 'int|in:0,1,2',
        ]);

        $dashboard->fill($validated);
        $dashboard->save();

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'Dashboard ' . htmlentities($dashboard->dashboard_name) . ' updated',
        ]);
    }

    public function destroy(Dashboard $dashboard): JsonResponse
    {
        $dashboard->widgets()->delete();
        $dashboard->delete();

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'Dashboard deleted',
        ]);
    }

    public function copy(Request $request, Dashboard $dashboard): JsonResponse
    {
        $this->validate($request, [
            'target_user_id' => 'required|exists:App\Models\User,user_id',
        ]);

        $target_user_id = $request->get('target_user_id');

        $this->authorize('copy', [$dashboard, $target_user_id]);

        $dashboard_copy = $dashboard->replicate()->fill([
            'user_id' => $target_user_id,
            'dashboard_name' => $dashboard->dashboard_name . '_' . Auth::user()->username,
        ]);

        if ($dashboard_copy->save()) {
            // copy widgets
            $dashboard->widgets->each(function (UserWidget $widget) use ($dashboard_copy, $target_user_id) {
                $dashboard_copy->widgets()->save($widget->replicate()->fill([
                    'user_id' => $target_user_id,
                ]));
            });

            return response()->json([
                'status' => 'ok',
                'message' => 'Dashboard copied',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'ERROR: Could not copy Dashboard',
        ]);
    }

    /**
     * @param  \App\Models\User  $user
     * @return \Illuminate\Support\Collection<\App\Models\Dashboard>
     */
    private function getAvailableDashboards(User $user): Collection
    {
        if ($this->dashboards === null) {
            $this->dashboards = Dashboard::allAvailable($user)->with('user:user_id,username')
                ->orderBy('dashboard_name')->get()->keyBy('dashboard_id');
        }

        return $this->dashboards;
    }
}
