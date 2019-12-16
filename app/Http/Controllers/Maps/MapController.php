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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Http\Controllers\Maps;

use App\Http\Controllers\Controller;
use LibreNMS\Config;

class MapController extends Controller
{
    protected function visOptions()
    {
        return Config::get('network_map_vis_options');
    }

    protected function nodeDisabledStyle()
    {
        return ['color' => [
                         'highlight' => [
                             'background' => Config::get('network_map_legend.di.node'),
                         ],
                         'border' => Config::get('network_map_legend.di.border'),
                         'background' => Config::get('network_map_legend.di.node'),
                     ],
               ];
    }

    protected function nodeDownStyle()
    {
        return ['color' => [
                         'highlight' => [
                             'background' => Config::get('network_map_legend.dn.node'),
                             'border' => Config::get('network_map_legend.dn.border'),
                         ],
                         'border' => Config::get('network_map_legend.dn.border'),
                         'background' => Config::get('network_map_legend.dn.node'),
                     ],
               ];
    }

    protected function nodeUpStyle()
    {
        return [];
    }
}
