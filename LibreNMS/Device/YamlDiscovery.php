<?php
/**
 * YamlDiscovery.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

/**
 * Created by IntelliJ IDEA.
 * User: murrant
 * Date: 12/2/17
 * Time: 2:23 PM
 */

namespace LibreNMS\Device;


use LibreNMS\OS;

class YamlDiscovery
{

    public static function preCache(OS $os)
    {
        // Pre-cache data for later use
        $pre_cache = array();
        $device = $os->getDevice();

        $pre_cache_file = 'includes/discovery/sensors/pre-cache/' . $device['os'] . '.inc.php';
        if (is_file($pre_cache_file)) {
            echo "Pre-cache {$device['os']}: ";
            include $pre_cache_file;
            echo PHP_EOL;
            d_echo($pre_cache);
        }

        // TODO change to exclude os with pre-cache php file, but just exclude them by hand for now (like avtech)
        if ($device['os'] == 'avtech') {
            return $pre_cache;
        }

        if (!empty($device['dynamic_discovery']['modules'])) {
            echo "Caching data: ";
            foreach ($device['dynamic_discovery']['modules'] as $module => $discovery_data) {
                echo " $module";
                foreach ($discovery_data as $key => $data_array) {
                    foreach ($data_array['data'] as $data) {
                        foreach ((array)$data['oid'] as $oid) {
                            if (!isset($pre_cache[$oid])) {
                                if (isset($data['snmp_flags'])) {
                                    $snmp_flag = $data['snmp_flags'];
                                } else {
                                    $snmp_flag = '-OeQUs';
                                }
                                $snmp_flag .= ' -Ih';
                                $pre_cache[$oid] = snmpwalk_cache_oid($device, $oid, $pre_cache[$oid], $device['dynamic_discovery']['mib'], null, $snmp_flag);
                            }
                        }
                    }
                }

            }
            echo PHP_EOL;
        }

        return $pre_cache;
    }
}
