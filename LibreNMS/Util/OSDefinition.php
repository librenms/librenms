<?php
/**
 * OSDefinition.php
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

use App\Models\Device;
use App\Models\Eventlog;
use LibreNMS\Config;
use Symfony\Component\Yaml\Yaml;

class OSDefinition
{
    public static $def_dir = '/includes/definitions/';
    public static $discovery_dir = '/includes/definitions/discovery/';

    private $os;
    private $discovery = null;

    public function __construct($os)
    {
        $this->os = $os;
    }

    public static function make($os)
    {
        return new static($os);
    }

    /**
     * Load the os definition for the device array and set type and os_group
     * $device['os'] must be set
     *
     * @param array $device
     */
    public static function populate(&$device)
    {
        if (!isset($device['os'])) {
            d_echo("No OS to load\n");
            return;
        }

        $os_def = new static($device['os']);
        $os_def->load();

        // Set type to a predefined type for the OS if it's not already set
        $loaded_type = Config::getOsSetting($device['os'], 'type');
        if ((!isset($device['attribs']['override_device_type']) && $device['attribs']['override_device_type'] != 1) && $loaded_type != $device['type']) {
            Eventlog::log('Device type changed ' . $device['type'] . ' => ' . $loaded_type, $device, 'system', 3);
            $device['type'] = $loaded_type;
            Device::query()->where('device_id', $device['device_id'])->update(['type' => $device['type']]);
            d_echo("Device type changed to " . $device['type'] . "!\n");
        }

        if ($loaded_group = Config::getOsSetting($device['os'], 'group')) {
            $device['os_group'] = $loaded_group;
        } else {
            unset($device['os_group']);
        }
    }

    /**
     * Load all OS, optionally load just the OS used by existing devices
     * Default cache time is 1 day. Controlled by os_def_cache_time.
     *
     * @param bool $existing Only load OS that have existing OS in the database
     * @param bool $cached Load os definitions from the cache file
     */
    public static function loadAll($existing = false, $cached = true)
    {
        if ($cached && self::cacheIsValid()) {
            // Cached
            $os_defs = unserialize(file_get_contents(self::getCacheFile()));

            if ($existing) {
                // remove unneeded os
                $os_defs = array_diff_key($os_defs, Device::query()->distinct()->value('os'));
            }

            Config::set('os', array_replace_recursive($os_defs, Config::get('os', [])));
        } else {
            // load from yaml
            $os_list = $existing ? Device::query()->distinct()->value('os') : glob((new static('*'))->getYamlFile());

            foreach ($os_list as $os) {
                // strip path if it is a filename (db os list will be unchanged)
                (new static(basename($os, '.yaml')))->load();
            }
        }
    }

    /**
     * Load the OS from Yaml into the config (if not already loaded)
     *
     * @param $os
     */
    public function load()
    {
        $os_key = 'os.' . $this->os;

        $file = $this->getYamlFile();

        if (!Config::has("$os_key.definition_loaded") && is_readable($file)) {
            $loaded_os = Yaml::parse(file_get_contents($file));

            Config::set(
                $os_key,
                Config::has($os_key) ? array_replace_recursive($loaded_os, Config::get($os_key)) : $loaded_os
            );

            Config::set("$os_key.definition_loaded", true);
        }
    }

    /**
     * Get the discovery data for this OS definition.
     * Cached after the first load
     *
     * @return array
     */
    public function discovery()
    {
        if (is_null($this->discovery)) {
            $file = $this->getDiscoveryYamlFile();
            $this->discovery = is_readable($file) ? Yaml::parse(file_get_contents($file)) : [];
        }

        return $this->discovery;
    }

    /**
     * * Update the OS cache file cache/os_defs.cache
     * @param bool $force
     * @return bool true if the cache was updated
     */
    public static function updateCache($force = false)
    {
        if ($force === true || !self::cacheIsValid()) {
            d_echo('Updating os_def.cache... ');

            // remove previously cached os settings and replace with user settings
            $config = ['os' => []]; // local $config variable, not global
            include Config::installDir() . "/config.php";
            Config::set('os', $config['os']);

            // load the os defs fresh from cache (merges with existing OS settings)
            self::loadAll(false, false);

            file_put_contents(self::getCacheFile(), serialize(Config::get('os')));
            d_echo("Done\n");
            return true;
        }

        return false;
    }

    /**
     * Get the OS definitions cache file location
     *
     * @return string
     */
    public static function getCacheFile()
    {
        return Config::installDir() . "/cache/os_defs.cache";
    }

    /**
     * Get the OS definition yaml file location for the given OS
     *
     * @return string
     */
    public function getYamlFile()
    {
        return Config::installDir() . self::$def_dir . $this->os . '.yaml';
    }

    /**
     * Get the OS discovery yaml file location for the give OS
     *
     * @return string
     */
    public function getDiscoveryYamlFile()
    {
        return Config::installDir() . self::$discovery_dir . $this->os . '.yaml';
    }

    private static function cacheIsValid()
    {
        $cache_file = self::getCacheFile();
        $cache_keep_time = Config::get('os_def_cache_time', 86400) - 7200; // 2hr buffer
        return is_file($cache_file) && time() - filemtime($cache_file) < $cache_keep_time;
    }
}
