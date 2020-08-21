<?php
/**
 * EventlogController.php
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

use App\Models\Device;
use Illuminate\Http\Request;

class EventlogController extends WidgetController
{
    protected $title = 'Eventlog';
    protected $defaults = [
        'title' => null,
        'device' => null,
        'device_group' => null,
        'eventtype' => null,
        'hidenavigation' => 0,
    ];

    public function getSettingsView(Request $request)
    {
        $data = $this->getSettings(true);

        $data['device'] = Device::hasAccess($request->user())->find($data['device']);

        return view('widgets.settings.eventlog', $data);
    }

    public function getView(Request $request)
    {
        return view('widgets.eventlog', $this->getSettings());
    }
}
