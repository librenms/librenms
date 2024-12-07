<?php
/**
 * CustomMapController.php
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
 * @copyright  2024 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\CustomMap;
use Illuminate\Http\Request;
use LibreNMS\Config;

class CustomMapController extends WidgetController
{
    protected $title = 'Custom Map';
    protected $defaults = [
        'title' => null,
        'custom_map' => null,
        'screenshot' => false,
    ];

    public function __construct()
    {
        $this->authorizeResource(CustomMap::class, 'map');
    }

    public function getView(Request $request)
    {
        $data = $this->getSettings(true);

        $data['map'] = CustomMap::find($data['custom_map']);
        if (! $data['map']) {
            return __('map.custom.widget.not_found');
        }
        $data['base_url'] = Config::get('base_url');
        $data['background_config'] = $data['map']->getBackgroundConfig();
        $data['map_conf'] = $data['map']->options;

        $scalex = (float) $request->dimensions['x'] / (float) $data['map']->width;
        $scaley = (float) $request->dimensions['y'] / (float) $data['map']->height;
        $data['scale'] = min($scalex, $scaley);

        return view('widgets.custom-map', $data);
    }

    public function getSettingsView(Request $request)
    {
        $data = $this->getSettings(true);
        $data['map'] = CustomMap::find($data['custom_map']);

        return view('widgets.settings.custom-map', $data);
    }
}
