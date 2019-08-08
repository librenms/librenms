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

use Illuminate\Http\Request;

class AlertsController extends WidgetController
{
    protected $title = 'Alerts';
    protected $defaults = [
        'title' => null,
        'device' => null,
        'acknowledged' => null,
        'fired' => null,
        'min_severity' => null,
        'state' => null,
        'device_group' => null,
        'proc' => 0,
        'location' => 1,
        'sort' => 1,
    ];

    public function getView(Request $request)
    {
        return view('widgets.alerts', $this->getSettings());
    }

    public function getSettingsView(Request $request)
    {
        $data = $this->getSettings(true);
        $data['severities'] = [
            // alert_rules.status is enum('ok','warning','critical')
            'ok' => 1,
            'warning' => 2,
            'critical' => 3,
            'ok only' => 4,
            'warning only' => 5,
            'critical only' => 6,
        ];
        $data['states'] = [
            // divined from librenms/alerts.php
            'recovered' => '0',
            'alerted' => '1',
            'acknowledged' => '2',
            'worse' => '3',
            'better' => '4',
        ];

        return view('widgets.settings.alerts', $data);
    }
}
