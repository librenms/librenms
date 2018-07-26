<?php
/**
 * Plugins.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @package    LibreNMS
 * @subpackage Plugins
 * @author     LibreNMS Group
 * @link       http://librenms.org
 * @copyright  2016
 */

namespace LibreNMS;

use App\Models\Plugin;
use LibreNMS\Exceptions\PluginLoadException;
use LibreNMS\Exceptions\FileNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * Handles loading of plugins
 *
 * @package    LibreNMS
 * @subpackage Plugins
 * @author     LibreNMS Group
 * @link       http://librenms.org
 * @copyright  2016
 * @version    1.41.1
 *
 * Supported hooks
 * <ul>
 *  <li>menu</li>
 *  <li>device_overview_container</li>
 *  <li>port_container</li>
 * </ul>
 */
class Plugins
{
    /**
     * Array of plugin hooks
     *
     * @var array
     */
    private static $plugins = null;

    /**
     * Start loading active plugins
     *
     * @return boolean
     */
    public static function start()
    {
        if (!is_null(self::$plugins)) {
            return false;
        }

        self::$plugins = [];
        if (!file_exists(self::getDir())) {
            return false;
        }

        $plugin_files = self::getActive();
        foreach ($plugin_files as $plugins) {
            $fn          = self::getFileNames($plugins['plugin_name']);
            $plugin_file = $fn['file'];
            $plugin_info = pathinfo($plugin_file);

            if ($plugin_info['extension'] !== 'php') {
                continue;
            }

            if (!is_file($plugin_file)) {
                continue;
            }

            try {
                self::load($plugin_file, $plugin_info['filename']);
            } catch (PluginLoadException $e) {
                Log::error($e->getMessage());
                self::deactivate($plugins['plugin_id']);
            }
        }
        return true;
    }

    /**
     * Load plugin
     *
     * @param  string $file       Full path and filename of plugin
     * @param  string $plugin_name Plugin name without any namespace
     * @return object|null
     */
    public static function load($file, $plugin_name)
    {
        $plugin = self::getPluginInstance($file, $plugin_name);
        if (is_null($plugin)) {
            throw new PluginLoadException('Plugin load failure : '.$plugin_name);
        }

        $class = get_class($plugin);
        $hooks = get_class_methods($class);

        foreach ((array) $hooks as $hook_name) {
            if ($hook_name == '_') {
                continue;
            }

            if (!key_exists($hook_name, self::$plugins)) {
                self::$plugins[$hook_name][] = $class;
                continue;
            }

            if (in_array($class, self::$plugins[$hook_name])) {
                continue;
            }

            self::$plugins[$hook_name][] = $class;
        }
        return $plugin;
    }

    /**
     * Get an instance of this plugin
     * Search various namespaces and include files if needed.
     *
     * @param string $file
     * @param string $plugin_name
     * @return object|null
     */
    private static function getPluginInstance($file, $plugin_name)
    {
        $ns_prefix = 'LibreNMS\\Plugins\\';
        $ns_psr4   = $ns_prefix.$plugin_name.'\\'.$plugin_name;
        $ns_plugin = $ns_prefix.$plugin_name;
        $ns_global = $plugin_name;

        if (class_exists($ns_psr4)) {
            return new $ns_psr4;
        }

        if (class_exists($ns_plugin)) {
            return new $ns_plugin;
        }

        // Include file because it's not psr4 (may have been included by previous class_exists calls
        include_once $file;

        if (class_exists($ns_global)) {
            return new $ns_global;
        }
        return null;
    }

    /**
     * Call hook for plugin.
     *
     * @param string $hook   Name of hook to call
     * @param array  $params Optional array of parameters for hook
     */
    public static function call($hook, $params = false)
    {
        self::start();

        if (empty(self::$plugins[$hook])) {
            return;
        }

        foreach (self::$plugins[$hook] as $name) {
            if (!is_array($params)) {
                @call_user_func(array($name, $hook));
            } else {
                @call_user_func_array(array($name, $hook), $params);
            }
        }
    }

    /**
     * Get all plugins implementing a specific hook.
     *
     * @param  string $hook Name of the hook to get count for
     * @return integer|boolean
     */
    public static function countHooks($hook)
    {
        // count all plugins implementing a specific hook
        self::start();
        if (!empty(self::$plugins[$hook])) {
            return count(self::$plugins[$hook]);
        } else {
            return false;
        }
    }

    /**
     * Get count of hooks.
     *
     * @return integer
     */
    public static function count()
    {
        self::start();
        return count(self::$plugins);
    }

    /**
     * Activate plugin
     *
     * @param integer $plugin_id ID to activate
     */
    public static function activate($plugin_id)
    {
        Plugin::where('plugin_id', $plugin_id)->update(['plugin_active' => 1]);
    }

    /**
     * Deactivate plugin
     *
     * @param integer $plugin_id ID to deactivate
     */
    public static function deactivate($plugin_id)
    {
        Plugin::where('plugin_id', $plugin_id)->update(['plugin_active' => 0]);
    }

    /**
     * Sync plugins table with folder.
     *
     *
     * return keys:
     * <ul>
     *   <li>active</li>
     *   <li>inactive</li>
     *   <li>removed</li>
     * </ul>
     * @return boolean|array
     */
    public static function sync()
    {
        $all               = self::getAll();
        $dir               = self::getDir();
        $stats['active']   = 0;
        $stats['inactive'] = 0;
        $stats['removed']  = 0;

        try {
            self::pathExist($dir);
        } catch (FileNotFoundException $e) {
            Log::error($e->getMessage());
            return false;
        }

        foreach ($all as $plugin) {
            if (file_exists($dir.'/'.$plugin['plugin_name'])) {
                continue;
            }

            Plugin::destroy($plugin['plugin_id']);
            $stats['removed'] ++;
        }

        $stats['active']   = self::getActiveCount();
        $stats['inactive'] = self::getInactiveCount();
        return $stats;
    }

    /**
     * Scan plugin directory for new plugins
     *
     * @param string $field Optional single field to return.
     *
     * return keys:
     * <ul>
     *   <li>active</li>
     *   <li>inactive</li>
     *   <li>installed</li>
     *   <li>inserted</li>
     * </ul>
     * @return boolean|array
     */
    public static function scan($field = null)
    {
        $stats['active']    = 0;
        $stats['inactive']  = 0;
        $stats['installed'] = 0;
        $stats['inserted']  = [];

        $dir = self::getDir();
        try {
            self::pathExist($dir);
        } catch (FileNotFoundException $e) {
            Log::error($e->getMessage());
            return false;
        }

        $plugin_files = scandir($dir);
        foreach ($plugin_files as $name) {
            if (!is_dir($dir.'/'.$name)) {
                continue;
            }

            if ($name == '.' || $name == '..') {
                continue;
            }

            $fn = self::getFileNames($name);
            if (!is_file($fn['file']) || !is_file($fn['include'])) {
                continue;
            }

            $plugin_id = self::getPluginByName($name);
            if (!empty($plugin_id)) {
                continue;
            }

            $record              = Plugin::create(['plugin_name' => $name, 'plugin_active' => '0'])->toArray();
            $stats['inserted'][] = $record;
            $stats['installed'] ++;
        }
        $stats['active']   = self::getActiveCount();
        $stats['inactive'] = self::getInactiveCount();

        if (!is_null($field) && key_exists($field, $stats)) {
            return $stats[$field];
        }
        return $stats;
    }

    /**
     * Get plugins directory
     *
     * @return string
     */
    public static function getDir()
    {
        return Config::get('plugin_dir');
    }

    /**
     * Get all installed plugins
     *
     * @return array
     */
    public static function getAll()
    {
        return Plugin::orderBy('plugin_name')->
                get()->
                toArray();
    }

    /**
     * Get only active plugins
     *
     * @return array
     */
    public static function getActive()
    {
        return Plugin::where('plugin_active', 1)->
                orderBy('plugin_name')->
                get()->
                toArray();
    }

    /**
     * Get count of active plugins
     *
     * @return integer
     */
    public static function getActiveCount()
    {
        return Plugin::where('plugin_active', '=', '1')->count();
    }

    /**
     * Get only inactive plugins
     *
     * @return array
     */
    public static function getInactive()
    {
        return Plugin::where('plugin_active', 0)->
                orderBy('plugin_name')->
                get()->
                toArray();
    }

    /**
     * Get count of inactive plugins
     *
     * @return integer
     */
    public static function getInactiveCount()
    {
        return Plugin::where('plugin_active', '=', '0')->count();
    }

    /**
     * Get plugin by name
     *
     * @param string $name Name of plugin
     * @return array
     */
    public static function getPluginByName($name)
    {
        return Plugin::where('plugin_name', $name)->get()->toArray();
    }

    /**
     * Create array of file names needed
     *
     * @param string $name Name of plugin
     * @return array
     */
    private static function getFileNames($name)
    {
        $dir     = self::getDir();
        $prefix  = $dir.'/'.$name.'/';
        $file    = $prefix.$name.'.php';
        $include = $prefix.$name.'.inc.php';
        return ['file' => $file, 'include' => $include];
    }

    /**
     * Make sure a path exist
     * @param string $dir
     * @return boolean
     * @throws FileNotFoundException
     */
    private static function pathExist($dir)
    {
        if (!file_exists($dir)) {
            throw new FileNotFoundException("Missing file or path : $dir");
        }
        return true;
    }
}
