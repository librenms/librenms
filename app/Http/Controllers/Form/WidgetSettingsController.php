<?php
/**
 * WidgetSettingsController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Form;

use App\Http\Controllers\Controller;
use App\Models\UserWidget;
use Illuminate\Http\Request;

class WidgetSettingsController extends Controller
{
    public function update(Request $request, $widget_settings)
    {
        $this->validate($request, [
            'settings' => 'array',
            'settings.refresh' => 'int|min:1',
        ]);

        $widget = UserWidget::with('dashboard')->findOrFail($widget_settings);
        $widget_settings = (array) $request->get('settings', []);
        unset($widget_settings['_token']);

        if (! $widget->dashboard->canWrite($request->user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'ERROR: You have no write-access to this dashboard',
            ]);
        }

        $widget->settings = $widget_settings;
        if ($widget->save()) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Updated widget settings',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'ERROR: Could not update',
        ]);
    }
}
