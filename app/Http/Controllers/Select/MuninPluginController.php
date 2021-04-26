<?php
/**
 * MuninPluginController.php
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

use App\Models\MuninPlugin;

class MuninPluginController extends SelectController
{
    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        return MuninPlugin::hasAccess($request->user())
            ->with(['device' => function ($query) {
                $query->select('device_id', 'hostname', 'sysName');
            }])
            ->select('mplug_id', 'mplug_type', 'device_id');
    }

    /**
     * @param MuninPlugin $munin_plugin
     */
    public function formatItem($munin_plugin)
    {
        return [
            'id' => $munin_plugin->mplug_id,
            'text' => $munin_plugin->device->shortDisplayName() . ' - ' . $munin_plugin->mplug_type,
        ];
    }
}
