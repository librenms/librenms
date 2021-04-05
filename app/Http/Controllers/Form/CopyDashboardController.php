<?php
/**
 * CopyDashboardController.php
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
 * @copyright  2020  Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\Dashboard;
use App\Models\UserWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CopyDashboardController extends Controller
{
    public function store(Request $request)
    {
        $target_user_id = $request->get('target_user_id');
        $dashboard_id = $request->get('dashboard_id');

        $dashboard = Dashboard::where(['dashboard_id' => $dashboard_id, 'user_id' => Auth::id()])->first();

        $success = true;

        if ((empty($dashboard)) || (empty($target_user_id))) {
            $success = false;
        }

        if ($success) {
            $dashboard_copy = $dashboard->replicate()->fill([
                'user_id' => $target_user_id,
                'dashboard_name' => $dashboard['dashboard_name'] .= '_' . Auth::user()->username,
            ]);
            $success = $dashboard_copy->save();
        }

        if ($success && isset($dashboard_copy)) {
            $widgets = UserWidget::where(['dashboard_id' => $dashboard_id, 'user_id' => Auth::id()])->get();

            foreach ($widgets as $widget) {
                $widget_copy = $widget->replicate()->fill([
                    'user_id' => $target_user_id,
                    'dashboard_id' => $dashboard_copy->dashboard_id,
                ]);
                $success &= $widget_copy->save();
            }
        }

        if ($success) {
            $status = 'ok';
            $message = 'Dashboard copied';
        } else {
            $status = 'error';
            $message = 'ERROR: Could not copy Dashboard';
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }
}
