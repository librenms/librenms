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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Application;
use App\Models\BgpPeer;
use App\Models\CefSwitching;
use App\Models\Component;
use App\Models\Device;
use App\Models\IsisAdjacency;
use App\Models\Mpls;
use App\Models\OspfInstance;
use App\Models\Port;
use App\Models\PrinterSupply;
use App\Models\Pseudowire;
use App\Models\Sensor;
use App\Models\Service;
use App\Models\Vrf;
use Cache;

class ObjectCache
{
    private static $cache_time = 300;

    public static function applications()
    {
        return Cache::remember('ObjectCache:applications_list:' . auth()->id(), self::$cache_time, function () {
            return Application::hasAccess(auth()->user())
                ->select('app_type', 'app_state', 'app_instance')
                ->groupBy('app_type', 'app_state', 'app_instance')
                ->get()
                ->sortBy('show_name', SORT_NATURAL | SORT_FLAG_CASE)
                ->groupBy('app_type');
        });
    }

    public static function routing()
    {
        return Cache::remember('ObjectCache:routing_counts:' . auth()->id(), self::$cache_time, function () {
            $user = auth()->user();

            return [
                'vrf' => Vrf::hasAccess($user)->count(),
                'mpls' => Mpls::hasAccess($user)->count(),
                'ospf' => OspfInstance::hasAccess($user)->count(),
                'isis' => IsisAdjacency::hasAccess($user)->count(),
                'cisco-otv' => Component::hasAccess($user)->where('type', 'Cisco-OTV')->count(),
                'bgp' => BgpPeer::hasAccess($user)->count(),
                'cef' => CefSwitching::hasAccess($user)->count(),
            ];
        });
    }

    public static function sensors()
    {
        return Cache::remember('ObjectCache:sensor_list:' . auth()->id(), self::$cache_time, function () {
            $sensor_classes = Sensor::hasAccess(auth()->user())->select('sensor_class')->groupBy('sensor_class')->orderBy('sensor_class')->get();

            $sensor_menu = [];
            foreach ($sensor_classes as $sensor_model) {
                /** @var Sensor $sensor_model */
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
                    'descr' => $sensor_model->classDescr(),
                ];
            }

            if (PrinterSupply::hasAccess(auth()->user())->exists()) {
                $sensor_menu[3] = [
                    [
                        'class' => 'toner',
                        'icon' => 'print',
                        'descr' => __('Toner'),
                    ],
                ];
            }

            ksort($sensor_menu); // ensure menu order

            return $sensor_menu;
        });
    }

    /**
     * @param int $device_id device id of the device to get counts for, 0 means all
     * @param array $fields array of counts to get. Valid options: total, up, down, ignored, shutdown, disabled, deleted, errored, pseudowire
     * @return mixed
     */
    public static function portCounts($fields = ['total'], $device_id = 0)
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = self::getPortCount($field, $device_id);
        }

        return $result;
    }

    private static function getPortCount($field, $device_id)
    {
        return Cache::remember("ObjectCache:port_{$field}_count:$device_id:" . auth()->id(), self::$cache_time, function () use ($field, $device_id) {
            $query = Port::hasAccess(auth()->user())->when($device_id, function ($query) use ($device_id) {
                $query->where('device_id', $device_id);
            });
            switch ($field) {
                case 'down':
                    return $query->isDown()->count();
                case 'up':
                    return $query->isUp()->count();
                case 'ignored':
                    return $query->isIgnored()->count();
                case 'shutdown':
                    return $query->isShutdown()->count();
                case 'disabled':
                    return $query->isDisabled()->count();
                case 'deleted':
                    return $query->isDeleted()->count();
                case 'errored':
                    return $query->hasErrors()->count();
                case 'pseudowire':
                    return Pseudowire::hasAccess(auth()->user())->count();
                case 'total':
                default:
                    return $query->isNotDeleted()->count();
            }
        });
    }

    /**
     * @param array $fields array of counts to get. Valid options: total, up, down, ignored, disabled
     * @return array
     */
    public static function deviceCounts($fields = ['total'])
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = self::getDeviceCount($field);
        }

        return $result;
    }

    private static function getDeviceCount($field)
    {
        return Cache::remember("ObjectCache:device_{$field}_count:" . auth()->id(), self::$cache_time, function () use ($field) {
            $query = Device::hasAccess(auth()->user());
            switch ($field) {
                case 'down':
                    return $query->isDown()->count();
                case 'up':
                    return $query->isUp()->count();
                case 'ignored':
                    return $query->isIgnored()->count();
                case 'disabled':
                    return $query->isDisabled()->count();
                case 'disable_notify':
                    return $query->isDisableNotify()->count();
                case 'total':
                default:
                    return $query->count();
            }
        });
    }

    /**
     * @param array $fields array of counts to get. Valid options: total, ok, warning, critical, ignored, disabled
     * @return array
     */
    public static function serviceCounts($fields = ['total'], $device_id = 0)
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = self::getServiceCount($field, $device_id);
        }

        return $result;
    }

    private static function getServiceCount($field, $device_id)
    {
        return Cache::remember("ObjectCache:service_{$field}_count:" . auth()->id(), self::$cache_time, function () use ($field, $device_id) {
            $query = Service::hasAccess(auth()->user())->when($device_id, function ($query) use ($device_id) {
                $query->where('device_id', $device_id);
            });
            switch ($field) {
                case 'ok':
                    return $query->isOk()->count();
                case 'warning':
                    return $query->isWarning()->count();
                case 'critical':
                    return $query->isCritical()->count();
                case 'ignored':
                    return $query->isIgnored()->count();
                case 'disabled':
                    return $query->isDisabled()->count();
                case 'total':
                default:
                    return $query->count();
            }
        });
    }
}
