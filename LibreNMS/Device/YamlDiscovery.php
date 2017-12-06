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

namespace LibreNMS\Device;

use LibreNMS\Interfaces\Discovery\DiscoveryItem;
use LibreNMS\OS;

class YamlDiscovery
{
    /**
     * @param OS $os
     * @param DiscoveryItem $class
     * @param $yaml_data
     * @return array
     */
    public static function discover(OS $os, $class, $yaml_data)
    {
        $pre_cache = $os->preCache();
        $items = array();

        // convert to class name for static call below
        if (is_object($class)) {
            $class = get_class($class);
        }

        foreach ($yaml_data as $first_key => $first_yaml) {
            if ($first_key == 'pre-cache') {
                continue;
            }

            // find the data array, we could already be at for simple modules
            if (isset($data['data'])) {
                $first_yaml = $first_yaml['data'];
            } elseif ($first_key !== 'data') {
                continue;
            }

            foreach ($first_yaml as $data) {

                $raw_data = (array)$pre_cache[$data['oid']];

                d_echo("Data {$data['oid']}: ");
                d_echo($raw_data);

                foreach ($raw_data as $index => $snmp_data) {
                    $current_data = array();

                    if (!isset($data['value'])) {
                        $data['value'] = $data['oid'];
                    }

                    foreach ($data as $name => $value) {
                        if ($name == '$oid') {
                            $current_data[$name] = $value;
                        } elseif (str_contains($value, '{{')) {
                            $current_data[$name] = YamlDiscovery::replaceValues($name, $index, $data, $pre_cache);
                        } else {
                            $current_data[$name] = dynamic_discovery_get_value($name, $index, $data, $pre_cache, $value);
                        }
                    }

                    $item = $class::fromYaml($os, $current_data);

                    if ($item->isValid()) {
                        $items[] = $item;
                    }
                }
            }
        }
        return $items;
    }



    public static function replaceValues($name, $index, $data, $pre_cache)
    {
        $value = dynamic_discovery_get_value($name, $index, $data, $pre_cache);
        if (is_null($value)) {
            $value = str_replace('{{ $index }}', $index, $data[$name]);
            preg_match_all('/{{ \$([a-zA-Z0-9.]+) }}/', $value, $matches);
            foreach ($matches[1] as $tmp_var) {
                $replace = dynamic_discovery_get_value($tmp_var, $index, $data, $pre_cache, null);
                if (!is_null($replace)) {
                    $value = str_replace("{{ \$$tmp_var }}", $replace, $value);
                }
            }
        }

        return $value;
    }


    /**
     * Helper function for dynamic discovery to search for data from pre_cached snmp data
     *
     * @param string $name The name of the field from the discovery data or just an oid
     * @param int $index The index of the current sensor
     * @param array $discovery_data The discovery data for the current sensor
     * @param array $pre_cache all pre-cached snmp data
     * @param mixed $default The default value to return if data is not found
     * @return mixed
     */
    public static function dynamic_discovery_get_value($name, $index, $discovery_data, $pre_cache, $default = null)
    {
        if (isset($discovery_data[$name])) {
            $name = $discovery_data[$name];
        }

        if (isset($pre_cache[$discovery_data['oid']][$index][$name])) {
            return $pre_cache[$discovery_data['oid']][$index][$name];
        }

        if (isset($pre_cache[$name])) {
            if (is_array($pre_cache[$name])) {
                if (isset($pre_cache[$name][$index][$name])) {
                    return $pre_cache[$name][$index][$name];
                } elseif (isset($pre_cache[$index][$name])) {
                    return $pre_cache[$index][$name];
                } elseif (count($pre_cache[$name]) === 1) {
                    return current($pre_cache[$name]);
                }
            } else {
                return $pre_cache[$name];
            }
        }

        return $default;
    }

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
                echo "$module ";
                foreach ($discovery_data as $key => $data_array) {
                    // find the data array, we could already be at for simple modules
                    if (isset($data_array['data'])) {
                        $data_array = $data_array['data'];
                    } elseif ($key !== 'data') {
                        continue;
                    }

                    foreach ($data_array as $data) {
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
