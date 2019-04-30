<?php
/**
 * ObjectCache.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Application;
use App\Models\BgpPeer;
use App\Models\CefSwitching;
use App\Models\Component;
use App\Models\OspfInstance;
use App\Models\Sensor;
use App\Models\Vrf;
use Cache;

class ObjectCache
{
    public static function applications()
    {
        return Cache::remember('ObjectCache:applications_list', 5, function () {
            return Application::hasAccess(auth()->user())
                ->select('app_type', 'app_instance')
                ->groupBy('app_type', 'app_instance')
                ->orderBy('app_type')
                ->get()
                ->groupBy('app_type');
        });
    }

    public static function routing()
    {
        return Cache::remember('ObjectCache:routing_counts', 5, function () {
            $user = auth()->user();
            return [
                'vrf' => Vrf::hasAccess($user)->count(),
                'ospf' => OspfInstance::hasAccess($user)->count(),
                'cisco-otv' => Component::hasAccess($user)->where('type', 'Cisco-OTV')->count(),
                'bgp' => BgpPeer::hasAccess($user)->count(),
                'cef' => CefSwitching::hasAccess($user)->count(),
            ];
        });
    }

    public static function sensors()
    {
        return Cache::remember('ObjectCache:sensor_list', 5, function () {
            $sensor_classes = Sensor::hasAccess(auth()->user())->select('sensor_class')->groupBy('sensor_class')->orderBy('sensor_class')->get();

            $sensor_menu = [];
            foreach ($sensor_classes as $sensor_model) {
//            /** @var Sensor $sensor_model */
            $class = $sensor_model->sensor_class;
                if (in_array($class, ['fanspeed', 'humidity', 'temperature', 'signal'])) {
                    // First group
                    $group = 0;
                } elseif (in_array($class, ['current', 'frequency', 'power', 'voltage', 'power_factor', 'power_consumed'])) {
                    // Second group
                    $group = 1;
                } else {
                    // anything else
                    $group = 2;
                }

                $sensor_menu[$group][] = [
                    'class' => $class,
                    'icon' => $sensor_model->icon(),
                    'descr' => $sensor_model->classDescr()
                ];
            }
            ksort($sensor_menu); // ensure menu order
            return $sensor_menu;
        });
    }
}
