<?php
/**
 * AlertsController.php
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

use App\Models\DeviceGroup;
use Illuminate\Http\Request;

class AlertsController extends WidgetController
{
    public $title = 'Alerts';

    public function getView(Request $request)
    {
        $id = $request->get('id');
        $settings = $this->getSettings();

        return view('widgets.alerts', compact('id', 'settings'));
    }

    public function getSettingsView(Request $request)
    {
        $settings = $this->getSettings();
        $severities = [
            // alert_rules.status is enum('ok','warning','critical')
            'ok' => 1,
            'warning' => 2,
            'critical' => 3,
            'ok only' => 4,
            'warning only' => 5,
            'critical only' => 6,
        ];
        $states = [
            // divined from librenms/alerts.php
            'recovered' => '0',
            'alerted' => '1',
            'acknowledged' => '2',
            'worse' => '3',
            'better' => '4',
        ];

        $data = [
            'id' => $request->get('id'),
            'acknowledged' => $settings->get('acknowledged'),
            'fired' => $settings->get('fired'),
            'severities' => $severities,
            'min_severity' => $settings->get('min_severity'),
            'states' => $states,
            'state' => $settings->get('state'),
            'device_group' => DeviceGroup::find($settings->get('group')),
            'proc' => $settings->get('proc'),
            'sort' => $settings->get('sort'),
        ];

        return view('widgets.settings.alerts', $data);
    }
}
