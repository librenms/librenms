<?php
/**
 * DependencyController.php
 *
 * Controller for graphing Relationships
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
 * @copyright  2019 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Config;

class DeviceDependencyController extends Controller
{
    // Device Dependency Map
    public function dependencyMap(Request $request): View
    {
        $group_id = $request->get('group');

        $group_name = DeviceGroup::where('id', '=', $group_id)->first('name');
        if (! empty($group_name)) {
            $group_name = $group_name->name;
        }

        $data = [
            'page_refresh' => Config::get('page_refresh', 300),
            'group_id' => $group_id,
            'options' => Config::get('network_map_dependencymap_vis_options') ?? Config::get('network_map_vis_options'),
            'group_name' => $group_name,
            'highlight_style' => [
                'color' => [
                    'highlight' => [
                        'border' => Config::get('network_map_legend.highlight.border'),
                    ],
                    'border' => Config::get('network_map_legend.highlight.border'),
                ],
                'borderWidth' => Config::get('network_map_legend.highlight.borderWidth'),
            ],
        ];

        return view('map.device-dependency', $data);
    }
}
