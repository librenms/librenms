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
use LibreNMS\Util\Clean;

class Services
{
    /**
     * List all available services from nagios plugins directory
     *
     * @return string[]
     */
    public static function list(): array
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

    public static function customCheckPath(string $check_name): string
    {
        $check = strtolower(Clean::fileName($check_name));

        return LibrenmsConfig::get('install_dir') . '/includes/services/check_' . $check . '.inc.php';
    }

    /**
     * Normalize DS name for RRD: 1 to 19 characters, [a-zA-Z0-9_]
     */
    public static function normalizeDsName(string $ds): string
    {
        if (preg_match('/^(?:.*:)?(rta|rtmin|rtmax|pl)$/', $ds, $matches)) {
            $normalized_ds = $matches[1];
        } else {
            $normalized_ds = preg_replace('/[^a-zA-Z0-9_]/', '', $ds);
        }

        return substr($normalized_ds, 0, 19);
    }

    /**
     * Parse standard Nagios performance data (string after '|').
     *
     * @return array<string, array{value: string|numeric, uom: string, full_name: string}>
     */
    public static function parsePerfdata(string $perf): array
    {
        // Valid values from: https://nagios-plugins.org/doc/guidelines.html#AEN200
        $valid_uom = ['us', 'ms', 'KB', 'MB', 'GB', 'TB', 'c', 's', '%', 'B'];

        // Split performance metrics into an array
        preg_match_all('/\'[^\']*\'\S*|\S+/', $perf, $perf_arr);
        $metrics = [];

        foreach ($perf_arr[0] as $string) {
            [$ds, $values] = array_pad(explode('=', trim($string)), 2, '');

            $value = $values ? explode(';', trim($values)) : [];
            $value = trim($value[0] ?? '');

            $uom = '';
            foreach ($valid_uom as $v) {
                if ((strlen($value) - strlen($v)) === strpos($value, $v)) {
                    $uom = $v;
                    $value = substr($value, 0, -strlen($v));
                    break;
                }
            }

            if ($ds !== '') {
                $ds = trim($ds, "'\"");
                $normalized_ds = self::normalizeDsName($ds);

                if (isset($metrics[$normalized_ds])) {
                    d_echo($normalized_ds . " collides with an existing index\n");
                    $perf_unique = false;
                    for ($i = 0; $i < 10; $i++) {
                        $tmp_ds_name = substr($normalized_ds, 0, 18) . $i;
                        if (! isset($metrics[$tmp_ds_name])) {
                            $normalized_ds = $tmp_ds_name;
                            $perf_unique = true;
                            break;
                        }
                    }
                    if (! $perf_unique) {
                        for ($i = 0; $i < 10; $i++) {
                            for ($j = 0; $j < 10; $j++) {
                                $tmp_ds_name = substr($normalized_ds, 0, 17) . $j . $i;
                                if (! isset($metrics[$tmp_ds_name])) {
                                    $normalized_ds = $tmp_ds_name;
                                    $perf_unique = true;
                                    break 2;
                                }
                            }
                        }
                    }
                    if (! $perf_unique) {
                        d_echo('could not generate a unique ds-name for ' . $ds . "\n");
                    }
                }

                d_echo('Perf Data - DS: ' . $normalized_ds . ', Value: ' . $value . ', UOM: ' . $uom . "\n");
                $metrics[$normalized_ds] = ['value' => $value, 'uom' => $uom, 'full_name' => $ds];
            } else {
                d_echo("Perf Data - None.\n");
            }
        }

        return $metrics;
    }

    /**
     * Parse generic key-value statistics from service check output.
     *
     * @return array<string, array{value: string|numeric, uom: string, full_name: string}>
     */
    public static function parseStats(string $output): array
    {
        $metrics = [];
        $valid_uom_regex = 'us|ms|KB|MB|GB|TB|B|c|s|%';

        if (preg_match_all('/(?<key>[a-zA-Z][a-zA-Z0-9_ ]*?)[:=]\s*(?<value>-?[0-9]+(?:\.[0-9]+)?)(?<uom>' . $valid_uom_regex . ')?(?=\s|$|;|,)/i', $output, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $rawKey = trim($match['key']);
                $val = trim($match['value']);
                $uom = trim($match['uom'] ?? '');

                $dsName = self::normalizeDsName($rawKey);
                if ($dsName !== '' && ! isset($metrics[$dsName])) {
                    $metrics[$dsName] = [
                        'value' => $val,
                        'uom' => $uom,
                        'full_name' => $rawKey,
                    ];
                }
            }
        }

        return $metrics;
    }
}
