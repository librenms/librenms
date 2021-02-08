<?php
/**
 * OS.php
 *
 * OS related functions (may belong in LibreNMS/OS, but here for now)
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use Log;
use Symfony\Component\Yaml\Yaml;

class OS
{
    /**
     * Load os from yaml into config if not already loaded, preserving user os config
     * @param string $os
     */
    public static function loadDefinition($os)
    {
        if (! Config::get("os.$os.definition_loaded")) {
            $yaml_file = base_path("/includes/definitions/$os.yaml");
            if (file_exists($yaml_file)) {
                $os_def = Yaml::parse(file_get_contents($yaml_file));

                Config::set("os.$os", array_replace_recursive($os_def, Config::get("os.$os", [])));
                Config::set("os.$os.definition_loaded", true);
            }
        }
    }

    /**
     * Load all OS, optionally load just the OS used by existing devices
     * Default cache time is 1 day. Controlled by os_def_cache_time.
     *
     * @param bool $existing Only load OS that have existing OS in the database
     * @param bool $cached Load os definitions from the cache file
     */
    public static function loadAllDefinitions($existing = false, $cached = true)
    {
        $install_dir = \LibreNMS\Config::get('install_dir');
        $cache_file = $install_dir . '/cache/os_defs.cache';
        if ($cached && is_file($cache_file) && (time() - filemtime($cache_file) < \LibreNMS\Config::get('os_def_cache_time'))) {
            // Cached
            $os_defs = unserialize(file_get_contents($cache_file));
            if ($existing) {
                // remove unneeded os
                $exists = Device::query()->distinct()->pluck('os')->flip()->all();
                $os_defs = array_intersect_key($os_defs, $exists);
            }
            \LibreNMS\Config::set('os', array_replace_recursive($os_defs, \LibreNMS\Config::get('os')));
        } else {
            // load from yaml
            if ($existing && Eloquent::isConnected()) {
                $os_list = Device::query()->distinct()->pluck('os');
            } else {
                $os_list = glob($install_dir . '/includes/definitions/*.yaml');
            }
            foreach ($os_list as $file) {
                $os = basename($file, '.yaml');
                self::loadDefinition($os);
            }
        }
    }

    /**
     * Update the OS cache file cache/os_defs.cache
     * @param bool $force
     * @return bool true if the cache was updated
     */
    public static function updateCache($force = false)
    {
        $install_dir = Config::get('install_dir');
        $cache_file = "$install_dir/cache/os_defs.cache";
        $cache_keep_time = Config::get('os_def_cache_time', 86400) - 7200; // 2hr buffer

        if ($force === true || ! is_file($cache_file) || time() - filemtime($cache_file) > $cache_keep_time) {
            Log::debug('Updating os_def.cache');

            // remove previously cached os settings and replace with user settings
            $config = ['os' => []]; // local $config variable, not global
            @include "$install_dir/config.php"; // FIXME load db settings too or don't load config.php
            Config::set('os', $config['os']);

            // load the os defs fresh from cache (merges with existing OS settings)
            self::loadAllDefinitions(false, false);

            file_put_contents($cache_file, serialize(Config::get('os')));

            return true;
        }

        return false;
    }
}
