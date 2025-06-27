<?php

/**
 * NetMapController.php
 *
 * Controller for dynamic network map base page
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
 * @copyright  2023 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Maps;

use App\Facades\LibrenmsConfig;
use App\Http\Controllers\Controller;
use App\Models\DeviceGroup;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Url;

class NetMapController extends Controller
{
    // Device Dependency Map
    public function netMap(Request $request, $vars = ''): View
    {
        $group_id = Url::parseOptions('group');

        $group_name = DeviceGroup::where('id', '=', $group_id)->first('name');
        if (! empty($group_name)) {
            $group_name = $group_name->name;
        }

        $data = [
            'page_refresh' => LibrenmsConfig::get('page_refresh', 300),
            'group_id' => $group_id,
            'options' => LibrenmsConfig::get('network_map_vis_options'),
            'group_name' => $group_name,
            'link_types' => LibrenmsConfig::get('network_map_items', ['xdp', 'mac']),
            'highlight_style' => [
                'color' => [
                    'highlight' => [
                        'border' => LibrenmsConfig::get('network_map_legend.highlight.border'),
                    ],
                    'border' => LibrenmsConfig::get('network_map_legend.highlight.border'),
                ],
                'borderWidth' => LibrenmsConfig::get('network_map_legend.highlight.borderWidth'),
            ],
        ];

        return view('map.netmap', $data);
    }
}
