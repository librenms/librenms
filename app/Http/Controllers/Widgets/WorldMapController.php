<?php
/**
 * WorldMapController.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use Illuminate\Http\Request;
use LibreNMS\Config;

class WorldMapController extends WidgetController
{
    protected $title = 'World Map';

    public function __construct()
    {
        $this->defaults = [
            'title' => null,
            'title_url' => Config::get('leaflet.tile_url', '{s}.tile.openstreetmap.org'),
            'init_lat' => Config::get('leaflet.default_lat', 51.4800),
            'init_lng' => Config::get('leaflet.default_lng', 0),
            'init_zoom' => Config::get('leaflet.default_zoom', 2),
            'group_radius' => 80,
            'status' => '0,1',
        ];
    }


    public function getView(Request $request)
    {
        $settings = $this->getSettings();
        $status = explode(',', $settings['status']);

        $settings['devices'] = [];

        return view('widgets.worldmap', $settings);
    }

    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.worldmap', $this->getSettings());
    }
}
