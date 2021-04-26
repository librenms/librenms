<?php
/**
 * OverviewController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use LibreNMS\Interfaces\UI\DeviceTab;
use Session;

class OverviewController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return true;
    }

    public function slug(): string
    {
        return 'overview';
    }

    public function icon(): string
    {
        return 'fa-lightbulb-o';
    }

    public function name(): string
    {
        return __('Overview');
    }

    public function data(Device $device): array
    {
        return [];
    }

    public static function setGraphWidth($graph = [])
    {
        // possibly the wrong spot for this
        if ($screen_width = Session::get('screen_width')) {
            if ($screen_width > 970) {
                $graph['width'] = round(($screen_width - 390) / 2, 0);
                $graph['height'] = round($graph['width'] / 3);
                $graph['lazy_w'] = $graph['width'] + 80;

                return $graph;
            }

            $graph['width'] = $screen_width - 190;
            $graph['height'] = round($graph['width'] / 3);
            $graph['lazy_w'] = $graph['width'] + 80;
        }

        return $graph;
    }
}
