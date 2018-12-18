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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Widgets;

use App\Models\Device;
use App\Models\Port;
use App\Models\Service;
use Illuminate\Http\Request;
use LibreNMS\Config;

abstract class DeviceSummaryController extends WidgetController
{
    protected $title = 'Device Summary';

    public function __construct()
    {
        // init defaults we need to check config, so do it in construct
        $this->defaults = [
            'show_services' => (int)Config::get('show_services', 1),
            'summary_errors' => (int)Config::get('summary_errors', 0)
        ];
    }

    public function getSettingsView(Request $request)
    {
        $settings = $this->getSettings();

        return view('widgets.settings.device-summary', $settings);
    }

    protected function getData(Request $request)
    {
        $data = $this->getSettings();
        $user = $request->user();

        $data['devices'] = [
            'count' => Device::hasAccess($user)->count(),
            'up' => Device::hasAccess($user)->isUp()->count(),
            'down' => Device::hasAccess($user)->isDown()->count(),
            'ignored' => Device::hasAccess($user)->isIgnored()->count(),
            'disabled' => Device::hasAccess($user)->isDisabled()->count(),
        ];

        $data['ports'] = [
            'count' => Port::hasAccess($user)->isNotDeleted()->count(),
            'up' => Port::hasAccess($user)->isNotDeleted()->isUp()->count(),
            'down' => Port::hasAccess($user)->isNotDeleted()->isDown()->count(),
            'ignored' => Port::hasAccess($user)->isNotDeleted()->isIgnored()->count(),
            'shutdown' => Port::hasAccess($user)->isNotDeleted()->isShutdown()->count(),
            'errored' => $data['summary_errors'] ? Port::hasAccess($user)->isNotDeleted()->hasErrors()->count() : -1,
        ];

        if ($data['show_services']) {
            $data['services'] = [
                'count' => Service::hasAccess($user)->count(),
                'up' => Service::hasAccess($user)->isUp()->count(),
                'down' => Service::hasAccess($user)->isDown()->count(),
                'ignored' => Service::hasAccess($user)->isIgnored()->count(),
                'disabled' => Service::hasAccess($user)->isDisabled()->count(),
            ];
        }

        return $data;
    }
}
