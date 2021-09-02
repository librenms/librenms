<?php
/**
 * DeviceSummaryController.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\ObjectCache;

abstract class DeviceSummaryController extends WidgetController
{
    protected $title = 'Device Summary';

    public function __construct()
    {
        // init defaults we need to check config, so do it in construct
        $this->defaults = [
            'show_services' => (int) Config::get('show_services', 1),
            'summary_errors' => (int) Config::get('summary_errors', 0),
        ];
    }

    public function getSettingsView(Request $request)
    {
        return view('widgets.settings.device-summary', $this->getSettings(true));
    }

    protected function getData(Request $request)
    {
        $data = $this->getSettings();

        $data['devices'] = ObjectCache::deviceCounts(['total', 'up', 'down', 'ignored', 'disabled', 'disable_notify']);

        $data['ports'] = $data['summary_errors'] ?
            ObjectCache::portCounts(['total', 'up', 'down', 'ignored', 'shutdown', 'errored']) :
            ObjectCache::portCounts(['total', 'up', 'down', 'ignored', 'shutdown']);

        if ($data['show_services']) {
            $data['services'] = ObjectCache::serviceCounts(['total', 'ok', 'critical', 'ignored', 'disabled']);
        }

        return $data;
    }
}
