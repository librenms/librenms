<?php
/**
 * ApplicationController.php
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

namespace App\Http\Controllers\Select;

use App\Models\Application;

class ApplicationController extends SelectController
{
    protected function rules()
    {
        return [
            'type' => 'nullable|string',
        ];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        $query = Application::hasAccess($request->user())->with(['device' => function ($query) {
            $query->select('device_id', 'hostname', 'sysName');
        }]);

        if ($type = $request->get('type')) {
            $query->where('app_type', $type);
        }

        return $query;
    }

    /**
     * @param Application $app
     */
    public function formatItem($app)
    {
        return [
            'id' => $app->app_id,
            'text' => $app->displayName() . ' - ' . $app->device->displayName(),
        ];
    }
}
