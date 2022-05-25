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
use App\Models\UserWidget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Dashboard::class, 'dashboard');
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
            'target_user_id' => 'required|exists:App\Models\User,user_id'
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
}
