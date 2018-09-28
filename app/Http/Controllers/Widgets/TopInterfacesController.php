<?php
/**
 * TopInterfacesController.php
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
use Illuminate\View\View;

class TopInterfacesController extends WidgetController
{

    /**
     * @param Request $request
     * @return View
     */
    public function getView(Request $request)
    {
        $data = $this->settingsWithDefaults();

        return view('widgets.top-interfaces', $data);
    }


    public function getSettingsView(Request $request)
    {
        $data = $this->settingsWithDefaults();
        $data['id'] = $request->get('id');

        return view('widgets.settings.top-interfaces', $data);
    }

    private function settingsWithDefaults()
    {
        $settings = $this->getSettings();

        return [
            'interface_count' => $settings->get('interface_count', 5),
            'time_interval' => $settings->get('time_interval', 15),
            'interface_filter' => $settings->get('interface_filter'),
        ];
    }
}
