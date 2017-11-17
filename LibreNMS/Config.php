<?php
/**
 * Config.php
 *
 * Config convenience class to access and set config variables.
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

namespace LibreNMS;

class Config
{
    /**
     * Get a config value, if non existent null (or default if set) will be returned
     *
     * @param string $key period separated config variable name
     * @param mixed $default optional value to return if the setting is not set
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        global $config;

        if (isset($config[$key])) {
            return $config[$key];
        }

        if (!str_contains($key, '.')) {
            return $default;
        }

        $keys = explode('.', $key);

        $curr = &$config;
        foreach ($keys as $k) {
            // do not add keys that don't exist
            if (!isset($curr[$k])) {
                return $default;
            }
            $curr = &$curr[$k];
        }

        if (is_null($curr)) {
            return $default;
        }

        return $curr;
    }

    /**
     * Get a setting from a device, if that is not set,
     * fall back to the global config setting prefixed by $global_prefix
     * The key must be the same for the global setting and the device setting.
     *
     * @param array $device Device array
     * @param string $key Name of setting to fetch
     * @param string $global_prefix specify where the global setting lives in the global config
     * @param mixed $default will be returned if the setting is not set on the device or globally
     * @return mixed
     */
    public static function getDeviceSetting($device, $key, $global_prefix = null, $default = null)
    {
        if (isset($device[$key])) {
            return $device[$key];
        }

        if (isset($global_prefix)) {
            $key = "$global_prefix.$key";
        }

        return self::get($key, $default);
    }

    /**
     * Get a setting from the $config['os'] array using the os of the given device
     * If that is not set, fallback to the same global config key
     *
     * @param string $os The os name
     * @param string $key period separated config variable name
     * @param mixed $default optional value to return if the setting is not set
     * @return mixed
     */
    public static function getOsSetting($os, $key, $default = null)
    {
        global $config;

        if ($os) {
            if (isset($config['os'][$os][$key])) {
                return $config['os'][$os][$key];
            }

            if (!str_contains($key, '.')) {
                return self::get($key, $default);
            }

            $os_key = "os.$os.$key";
            if (self::has($os_key)) {
                return self::get($os_key);
            }
        }

        return self::get($key, $default);
    }

    /**
     * Get the merged array from the global and os settings for the specified key.
     * Removes any duplicates.
     * When the arrays have keys, os settings take precedence over global settings
     *
     * @param string $os The os name
     * @param string $key period separated config variable name
     * @param array $default optional array to return if the setting is not set
     * @return array
     */
    public static function getCombined($os, $key, $default = array())
    {
        global $config;

        if (!self::has($key)) {
            return self::get("os.$os.$key", $default);
        }

        if (!isset($config['os'][$os][$key])) {
            if (!str_contains($key, '.')) {
                return self::get($key, $default);
            }
            if (!self::has("os.$os.$key")) {
                return self::get($key, $default);
            }
        }

        return array_unique(array_merge(
            (array)self::get($key, $default),
            (array)self::getOsSetting($os, $key, $default)
        ));
    }

    /**
     * Set a variable in the global config
     *
     * @param mixed $key period separated config variable name
     * @param mixed $value
     * @param bool $persist set the setting in the database so it persists across runs
     * @param string $default default (only set when initially created)
     * @param string $descr webui description (only set when initially created)
     * @param string $group webui group (only set when initially created)
     * @param string $sub_group webui subgroup (only set when initially created)
     */
    public static function set($key, $value, $persist = false, $default = '', $descr = '', $group = '', $sub_group = '')
    {
        global $config;

        if ($persist) {
            $res = dbUpdate(array('config_value' => $value), 'config', '`config_name`=?', array($key));
            if (!$res && !dbFetchCell('SELECT 1 FROM `config` WHERE `config_name`=?', array($key))) {
                $insert = array(
                    'config_name' => $key,
                    'config_value' => $value,
                    'config_default' => $default,
                    'config_descr' => $descr,
                    'config_group' => $group,
                    'config_sub_group' => $sub_group,
                );
                dbInsert($insert, 'config');
            }
        }

        $keys = explode('.', $key);

        $curr = &$config;
        foreach ($keys as $k) {
            $curr = &$curr[$k];
        }

        $curr = $value;
    }

    /**
     * Check if a setting is set
     *
     * @param string $key period separated config variable name
     * @return bool
     */
    public static function has($key)
    {
        global $config;

        if (isset($config[$key])) {
            return true;
        }

        if (!str_contains($key, '.')) {
            return false;
        }

        $keys = explode('.', $key);
        $last = array_pop($keys);

        $curr = &$config;
        foreach ($keys as $k) {
            // do not add keys that don't exist
            if (!isset($curr[$k])) {
                return false;
            }
            $curr = &$curr[$k];
        }

        return is_array($curr) && isset($curr[$last]);
    }
}
