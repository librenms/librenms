<?php

/*
 * Services.php
 *
 * Nagios services helper
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\Eventlog;
use App\Models\Service as ServiceModel;
use LibreNMS\Enum\Severity;

class Services
{
    /**
     * List all available services from nagios plugins directory
     *
     * @return array
     */
    public static function list()
    {
        $services = [];
        if (is_dir(LibrenmsConfig::get('nagios_plugins'))) {
            foreach (scandir(LibrenmsConfig::get('nagios_plugins')) as $file) {
                if (str_starts_with($file, 'check_')) {
                    $services[] = substr($file, 6);
                }
            }
        }

        return $services;
    }

    /**
     * Create a service entry for a device.
     *
     * Mirrors the legacy global add_service() helper.
     *
     * @param  array|int|\App\Models\Device  $device
     */
    public static function addService($device, string $type, string $desc, string $ip = '', string $param = '', int $ignore = 0, int $disabled = 0, $template_id = '', string $name = '')
    {
        $deviceModel = DeviceCache::get(is_array($device) ? $device['device_id'] : $device);

        if (empty($ip)) {
            $ip = $deviceModel->pollerTarget();
        }

        $insert = [
            'device_id' => $deviceModel->device_id,
            'service_ip' => $ip,
            'service_type' => $type,
            'service_desc' => $desc,
            'service_param' => $param,
            'service_ignore' => $ignore,
            'service_status' => 3,
            'service_message' => 'Service not yet checked',
            'service_ds' => '{}',
            'service_disabled' => $disabled,
            'service_template_id' => $template_id,
            'service_name' => $name,
        ];

        return ServiceModel::create($insert);
    }

    /**
     * Discover (auto-add) a service for a device if it does not already exist.
     *
     * @param  array|int|\App\Models\Device  $device
     */
    public static function discover($device, string $service): bool
    {
        // normalize device id
        if (is_array($device)) {
            $deviceId = $device['device_id'] ?? null;
        } elseif (is_object($device) && isset($device->device_id)) {
            $deviceId = $device->device_id;
        } else {
            $deviceId = $device;
        }

        if (! $deviceId) {
            return false;
        }

        if (ServiceModel::query()->where('service_type', $service)->where('device_id', $deviceId)->doesntExist()) {
            // create the service with standard defaults
            self::addService($device, $service, "$service Monitoring (Auto Discovered)", '', '', 0, 0, 0, "AUTO: $service");
            Eventlog::log('Autodiscovered service: type ' . $service, $deviceId, 'service', Severity::Info);

            return true;
        }

        return false;
    }
}
