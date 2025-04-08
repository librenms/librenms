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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use App\Facades\LibrenmsConfig;

// not yet: @deprecated Please use the facade App\Facades\LibrenmsConfig instead
class Config
{
    /**
     * Get the config setting definitions
     *
     * @return array
     */
    public static function getDefinitions(): array
    {
        return LibrenmsConfig::getDefinitions();
    }

    /**
     * Get a config value, if non-existent null (or default if set) will be returned
     *
     * @param  string  $key  period separated config variable name
     * @param  mixed  $default  optional value to return if the setting is not set
     * @return mixed
     */
    public static function get($key, $default = null): mixed
    {
        return LibrenmsConfig::get($key, $default);
    }

    /**
     * Unset a config setting
     * or multiple
     *
     * @param  string|array  $key
     */
    public static function forget($key): void
    {
        LibrenmsConfig::forget($key);
    }

    /**
     * Get a setting from a device, if that is not set,
     * fall back to the global config setting prefixed by $global_prefix
     * The key must be the same for the global setting and the device setting.
     *
     * @param  array  $device  Device array
     * @param  string  $key  Name of setting to fetch
     * @param  string  $global_prefix  specify where the global setting lives in the global config
     * @param  mixed  $default  will be returned if the setting is not set on the device or globally
     * @return mixed
     */
    public static function getDeviceSetting($device, $key, $global_prefix = null, $default = null): mixed
    {
        return LibrenmsConfig::getDeviceSetting($device, $key, $global_prefix, $default);
    }

    /**
     * Get a setting from the $config['os'] array using the os of the given device
     *
     * @param  string  $os  The os name
     * @param  string  $key  period separated config variable name
     * @param  mixed  $default  optional value to return if the setting is not set
     * @return mixed
     */
    public static function getOsSetting($os, $key, $default = null): mixed
    {
        return LibrenmsConfig::getOsSetting($os, $key, $default);
    }

    /**
     * Get the merged array from the global and os settings for the specified key.
     * Removes any duplicates.
     * When the arrays have keys, os settings take precedence over global settings
     *
     * @param  string|null  $os  The os name
     * @param  string  $key  period separated config variable name
     * @param  string  $global_prefix  prefix for global setting
     * @param  array  $default  optional array to return if the setting is not set
     * @return array
     */
    public static function getCombined(?string $os, string $key, string $global_prefix = '', array $default = []): array
    {
        return LibrenmsConfig::getCombined($os, $key, $global_prefix, $default);
    }

    /**
     * Set a variable in the global config
     *
     * @param  mixed  $key  period separated config variable name
     * @param  mixed  $value
     */
    public static function set($key, $value): void
    {
        LibrenmsConfig::set($key, $value);
    }

    /**
     * Save setting to persistent storage.
     *
     * @param  mixed  $key  period separated config variable name
     * @param  mixed  $value
     * @return bool if the save was successful
     */
    public static function persist($key, $value): bool
    {
        return LibrenmsConfig::persist($key, $value);
    }

    /**
     * Forget a key and all it's descendants from persistent storage.
     * This will effectively set it back to default.
     *
     * @param  string  $key
     * @return int|false
     */
    public static function erase($key): bool|int
    {
        return LibrenmsConfig::erase($key);
    }

    /**
     * Check if a setting is set
     *
     * @param  string  $key  period separated config variable name
     * @return bool
     */
    public static function has($key): bool
    {
        return LibrenmsConfig::has($key);
    }

    /**
     * Serialise the whole configuration to json for use in external processes.
     *
     * @return string
     */
    public static function toJson(): string
    {
        return LibrenmsConfig::toJson();
    }

    /**
     * Get the full configuration array
     *
     * @return array
     */
    public static function getAll(): array
    {
        return LibrenmsConfig::getAll();
    }

    /**
     * Locate the actual path of a binary
     *
     * @param  string  $binary
     * @return mixed
     */
    public static function locateBinary($binary): mixed
    {
        return LibrenmsConfig::locateBinary($binary);
    }
}
