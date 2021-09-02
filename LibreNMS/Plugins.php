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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>
 *
 * @author     LibreNMS Group
 * @link       https://www.librenms.org
 * @copyright  2016
 */

namespace LibreNMS;

use App\Models\Plugin;
use Log;

/**
 * Handles loading of plugins
 *
 * @author     LibreNMS Group
 * @link       https://www.librenms.org
 * @copyright  2016
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
     * @var array|null
     */
    private static $plugins = null;

    /**
     * Start loading active plugins
     *
     * @return bool
     */
    public static function start()
    {
        if (! is_null(self::$plugins)) {
            return false;
        }

        self::$plugins = [];
        $plugin_dir = Config::get('plugin_dir');

        if (! file_exists($plugin_dir)) {
            return false;
        }

        $plugin_files = Plugin::isActive()->get()->toArray();
        foreach ($plugin_files as $plugins) {
            $plugin_file = $plugin_dir . '/' . $plugins['plugin_name'] . '/' . $plugins['plugin_name'] . '.php';
            $plugin_info = pathinfo($plugin_file);

            if ($plugin_info['extension'] !== 'php') {
                continue;
            }

            if (! is_file($plugin_file)) {
                continue;
            }

            self::load($plugin_file, $plugin_info['filename']);
        }

        return true;
    }

    /**
     * Load plugin
     *
     * @param  string $file       Full path and filename of plugin
     * @param  string $pluginName Plugin name without any namespace
     * @return object|null
     */
    public static function load($file, $pluginName)
    {
        chdir(Config::get('install_dir') . '/html');
        $plugin = self::getInstance($file, $pluginName);

        if (! is_null($plugin)) {
            $class = get_class($plugin);
            $hooks = get_class_methods($class);

            foreach ((array) $hooks as $hookName) {
                if ($hookName[0] != '_') {
                    self::$plugins[$hookName][] = $class;
                }
            }
        }

        chdir(Config::get('install_dir'));

        return $plugin;
    }

    /**
     * Get an instance of this plugin
     * Search various namespaces and include files if needed.
     *
     * @param string $file
     * @param string $pluginName
     * @return object|null
     */
    private static function getInstance($file, $pluginName)
    {
        $ns_prefix = 'LibreNMS\\Plugins\\';
        $ns_psr4 = $ns_prefix . $pluginName . '\\' . $pluginName;
        $ns_plugin = $ns_prefix . $pluginName;
        $ns_global = $pluginName;

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
     * Get all plugins implementing a specific hook.
     *
     * @param  string $hook Name of the hook to get count for
     * @return int|bool
     */
    public static function countHooks($hook)
    {
        // count all plugins implementing a specific hook
        self::start();
        if (! empty(self::$plugins[$hook])) {
            return count(self::$plugins[$hook]);
        } else {
            return false;
        }
    }

    /**
     * Call hook for plugin.
     *
     * @param string $hook Name of hook to call
     * @param array|false $params Optional array of parameters for hook
     * @return string
     */
    public static function call($hook, $params = false)
    {
        chdir(Config::get('install_dir') . '/html');
        self::start();

        ob_start();
        if (! empty(self::$plugins[$hook])) {
            foreach (self::$plugins[$hook] as $name) {
                try {
                    if (! is_array($params)) {
                        @call_user_func([$name, $hook]);
                    } else {
                        @call_user_func_array([$name, $hook], $params);
                    }
                } catch (\Exception $e) {
                    Log::error($e);
                }
            }
        }
        $output = ob_get_contents();
        ob_end_clean();

        chdir(Config::get('install_dir'));

        return $output;
    }

    /**
     * Get count of hooks.
     *
     * @return int
     */
    public static function count()
    {
        self::start();

        return count(self::$plugins);
    }
}
