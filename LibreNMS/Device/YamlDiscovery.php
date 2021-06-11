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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Device;

use Cache;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Exceptions\InvalidOidException;
use LibreNMS\Interfaces\Discovery\DiscoveryItem;
use LibreNMS\OS;

class YamlDiscovery
{
    private static $cache_time = 1800; // 30 min, Used for oid translation cache

    /**
     * @param OS $os
     * @param DiscoveryItem|string $class
     * @param array $yaml_data
     * @return array
     */
    public static function discover(OS $os, $class, $yaml_data)
    {
        $pre_cache = $os->preCache();
        $device = $os->getDeviceArray();
        $items = [];

        // convert to class name for static call below
        if (is_object($class)) {
            $class = get_class($class);
        }

        d_echo('YAML Discovery Data: ');
        d_echo($yaml_data);

        foreach ($yaml_data as $first_key => $first_yaml) {
            if ($first_key == 'pre-cache') {
                continue;
            }

            $group_options = isset($first_yaml['options']) ? $first_yaml['options'] : [];

            // find the data array, we could already be at for simple modules
            if (isset($data['data'])) {
                $first_yaml = $first_yaml['data'];
            } elseif ($first_key !== 'data') {
                continue;
            }

            foreach ($first_yaml as $data) {
                $raw_data = (array) $pre_cache[$data['oid']];

                d_echo("Data {$data['oid']}: ");
                d_echo($raw_data);

                $count = 0;
                foreach ($raw_data as $index => $snmp_data) {
                    $count++;
                    $current_data = [];

                    // fall back to the fetched oid if value is not specified.  Useful for non-tabular data.
                    if (! isset($data['value'])) {
                        $data['value'] = $data['oid'];
                    }

                    // determine numeric oid automatically if not specified
                    if (! isset($data['num_oid'])) {
                        try {
                            $data['num_oid'] = static::computeNumericalOID($device, $data);
                        } catch (\Exception $e) {
                            d_echo('Error: We cannot find a numerical OID for ' . $data['value'] . '. Skipping this one...');
                            continue;
                        }
                    }

                    foreach ($data as $name => $value) {
                        if (in_array($name, ['oid', 'skip_values', 'snmp_flags'])) {
                            $current_data[$name] = $value;
                        } elseif (Str::contains($value, '{{')) {
                            // replace embedded values
                            $current_data[$name] = static::replaceValues($name, $index, $count, $data, $pre_cache);
                        } else {
                            // replace references to data
                            $current_data[$name] = static::getValueFromData($name, $index, $data, $pre_cache, $value);
                        }
                    }

                    if (static::canSkipItem($current_data['value'], $index, $current_data, $group_options, $snmp_data)) {
                        continue;
                    }

                    $item = $class::fromYaml($os, $index, $current_data);

                    if ($item->isValid()) {
                        $items[] = $item;
                    }
                }
            }
        }

        return $items;
    }

    /**
     * @param array $device Device we are working on
     * @param array $data Array derived from YAML
     * @return string
     */
    public static function computeNumericalOID($device, $data)
    {
        d_echo('Info: Trying to find a numerical OID for ' . $data['value'] . '.');
        $search_mib = $device['dynamic_discovery']['mib'];
        $mib_prefix_data_oid = Str::before($data['oid'], '::');
        if (! empty($mib_prefix_data_oid) && empty(Str::before($data['value'], '::'))) {
            // We should search value in this mib first, as it is explicitely specified
            $search_mib = $mib_prefix_data_oid . ':' . $search_mib;
        }

        try {
            $num_oid = static::oidToNumeric($data['value'], $device, $search_mib, $device['mib_dir']);
        } catch (\Exception $e) {
            throw $e;
        }

        d_echo('Info: We found numerical oid for ' . $data['value'] . ': ' . $num_oid);

        return $num_oid . '.{{ $index }}';
    }

    /**
     * @param string $name Name of the field in yaml
     * @param string $index index in the snmp table
     * @param int $count current count of snmp table entries
     * @param array $def yaml definition
     * @param array $pre_cache snmp data fetched from device
     * @return mixed|string|string[]|null
     */
    public static function replaceValues($name, $index, $count, $def, $pre_cache)
    {
        $value = static::getValueFromData($name, $index, $def, $pre_cache);

        if (is_null($value)) {
            // built in replacements
            $search = [
                '{{ $index }}',
                '{{ $count }}',
            ];
            $replace = [
                $index,
                $count,
            ];

            // prepare the $subindexX match variable replacement
            foreach (explode('.', $index) as $pos => $subindex) {
                $search[] = '{{ $subindex' . $pos . ' }}';
                $replace[] = $subindex;
            }

            $value = str_replace($search, $replace, $def[$name] ?? '');

            // search discovery data for values
            $value = preg_replace_callback('/{{ \$?([a-zA-Z0-9\-.:]+) }}/', function ($matches) use ($index, $def, $pre_cache) {
                $replace = static::getValueFromData($matches[1], $index, $def, $pre_cache, null);
                if (is_null($replace)) {
                    d_echo('Warning: No variable available to replace ' . $matches[1] . ".\n");

                    return ''; // remove the unavailable variable
                }

                return $replace;
            }, $value);
        }

        return $value;
    }

    /**
     * Helper function for dynamic discovery to search for data from pre_cached snmp data
     *
     * @param string $name The name of the field from the discovery data or just an oid
     * @param string|int $index The index of the current sensor
     * @param array $discovery_data The discovery data for the current sensor
     * @param array $pre_cache all pre-cached snmp data
     * @param mixed $default The default value to return if data is not found
     * @return mixed
     */
    public static function getValueFromData($name, $index, $discovery_data, $pre_cache, $default = null)
    {
        if (isset($discovery_data[$name])) {
            $name = $discovery_data[$name];
        }

        if (! is_array($discovery_data['oid']) && isset($pre_cache[$discovery_data['oid']][$index]) && isset($pre_cache[$discovery_data['oid']][$index][$name])) {
            return $pre_cache[$discovery_data['oid']][$index][$name];
        }

        if (isset($pre_cache[$index][$name])) {
            return $pre_cache[$index][$name];
        }

        //create the sub-index values in order to try to match them with precache
        $sub_indexes = explode('.', $index);
        // parse sub_index options name with trailing colon and index
        $sub_index = 0;
        $sub_index_end = null;
        if (preg_match('/^(.+):(\d+)(?:-(\d+))?$/', $name, $matches)) {
            [,$name, $sub_index, $sub_index_end] = $matches;
        }

        if (isset($pre_cache[$name]) && ! is_numeric($name)) {
            if (is_array($pre_cache[$name])) {
                if (isset($pre_cache[$name][$index][$name])) {
                    return $pre_cache[$name][$index][$name];
                } elseif (isset($pre_cache[$name][$index])) {
                    return $pre_cache[$name][$index];
                } elseif (count($pre_cache[$name]) === 1 && ! is_array(current($pre_cache[$name]))) {
                    return current($pre_cache[$name]);
                } elseif (isset($sub_indexes[$sub_index])) {
                    if ($sub_index_end) {
                        $multi_sub_index = implode('.', array_slice($sub_indexes, $sub_index, $sub_index_end));
                        if (isset($pre_cache[$name][$multi_sub_index][$name])) {
                            return $pre_cache[$name][$multi_sub_index][$name];
                        }
                    }

                    if (isset($pre_cache[$name][$sub_indexes[$sub_index]][$name])) {
                        return $pre_cache[$name][$sub_indexes[$sub_index]][$name];
                    }
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
        $pre_cache = [];
        $device = $os->getDeviceArray();

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

        if (! empty($device['dynamic_discovery']['modules'])) {
            echo 'Caching data: ';
            foreach ($device['dynamic_discovery']['modules'] as $module => $discovery_data) {
                echo "$module ";
                foreach ($discovery_data as $key => $data_array) {
                    // find the data array, we could already be at for simple modules
                    if (isset($data_array['data'])) {
                        $data_array = $data_array['data'];
                    } elseif ($key !== 'data') {
                        continue;
                    }

                    $saved_nobulk = Config::getOsSetting($os->getName(), 'nobulk', false);

                    foreach ($data_array as $data) {
                        foreach ((array) $data['oid'] as $oid) {
                            if (! array_key_exists($oid, $pre_cache)) {
                                if (isset($data['snmp_flags'])) {
                                    $snmp_flag = Arr::wrap($data['snmp_flags']);
                                } elseif (Str::contains($oid, '::')) {
                                    $snmp_flag = ['-OteQUS'];
                                } else {
                                    $snmp_flag = ['-OteQUs'];
                                }
                                $snmp_flag[] = '-Ih';

                                // disable bulk request for specific data
                                if (! empty($data['nobulk'])) {
                                    Config::set('os.' . $os->getName() . '.nobulk', true);
                                }

                                $mib = $device['dynamic_discovery']['mib'];
                                $pre_cache[$oid] = snmpwalk_cache_oid($device, $oid, $pre_cache[$oid] ?? [], $mib, null, $snmp_flag);

                                Config::set('os.' . $os->getName() . '.nobulk', $saved_nobulk);
                            }
                        }
                    }
                }
            }
            echo PHP_EOL;
        }

        return $pre_cache;
    }

    /**
     * Check to see if we should skip this discovery item
     *
     * @param mixed $value
     * @param array $yaml_item_data The data key from this item
     * @param array $group_options The options key from this group of items
     * @param array $pre_cache The pre-cache data array
     * @return bool
     */
    public static function canSkipItem($value, $index, $yaml_item_data, $group_options, $pre_cache = [])
    {
        $skip_values = array_replace((array) ($group_options['skip_values'] ?? []), (array) ($yaml_item_data['skip_values'] ?? []));

        foreach ($skip_values as $skip_value) {
            if (is_array($skip_value) && $pre_cache) {
                // Dynamic skipping of data
                $op = $skip_value['op'] ?? '!=';
                $tmp_value = static::getValueFromData($skip_value['oid'], $index, $yaml_item_data, $pre_cache);
                if (Str::contains($skip_value['oid'], '.')) {
                    [$skip_value['oid'], $targeted_index] = explode('.', $skip_value['oid'], 2);
                    $tmp_value = static::getValueFromData($skip_value['oid'], $targeted_index, $yaml_item_data, $pre_cache);
                }
                if (compare_var($tmp_value, $skip_value['value'], $op)) {
                    return true;
                }
            }
            if ($value == $skip_value) {
                return true;
            }
        }

        $skip_value_lt = array_replace((array) ($group_options['skip_value_lt'] ?? []), (array) ($yaml_item_data['skip_value_lt'] ?? []));
        foreach ($skip_value_lt as $skip_value) {
            if ($value < $skip_value) {
                return true;
            }
        }

        $skip_value_gt = array_replace((array) ($group_options['skip_value_gt'] ?? []), (array) ($yaml_item_data['skip_value_gt'] ?? []));
        foreach ($skip_value_gt as $skip_value) {
            if ($value > $skip_value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Translate an oid to numeric format (if already numeric, return as-is)
     *
     * @param string $oid
     * @param array|null $device
     * @param string $mib
     * @param string|null $mibdir
     * @return string numeric oid
     * @throws \LibreNMS\Exceptions\InvalidOidException
     */
    public static function oidToNumeric($oid, $device = null, $mib = 'ALL', $mibdir = null)
    {
        if (self::oidIsNumeric($oid)) {
            return $oid;
        }
        $key = 'YamlDiscovery:oidToNumeric:' . $mibdir . '/' . $mib . '/' . $oid;
        if (Cache::has($key)) {
            $numeric_oid = Cache::get($key);
        } else {
            foreach (explode(':', $mib) as $mib_name) {
                $numeric_oid = snmp_translate($oid, $mib_name, $mibdir, null, $device);
                if ($numeric_oid) {
                    break;
                }
            }
        }

        //Store the value
        Cache::put($key, $numeric_oid, self::$cache_time);

        if (empty($numeric_oid)) {
            throw new InvalidOidException("Unable to translate oid $oid");
        }

        return $numeric_oid;
    }

    public static function oidIsNumeric($oid)
    {
        return (bool) preg_match('/^[.\d]+$/', $oid);
    }
}
