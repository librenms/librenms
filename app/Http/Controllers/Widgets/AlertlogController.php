<?php
/**
 * AlertlogController.php
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

class AlertlogController extends WidgetController
{
    protected $title = 'Alert history';
    protected $defaults = [
        'title' => null,
        'device_id' => '',
        'state' => -1,
        'min_severity' => null,
        'hidenavigation' => 0,
    ];

    public function getView(Request $request)
    {
        return view('widgets.alertlog', $this->getSettings());
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
        return view('widgets.settings.alertlog', $data);
    }
}
