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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use App\Models\GraphType;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\DB\Eloquent;
use LibreNMS\Util\Debug;
use Log;

class Config
{
    private static $config;

    /**
     * Load the config, if the database connected, pull in database settings.
     *
     * return &array
     */
    public static function load()
    {
        // don't reload the config if it is already loaded, reload() should be used for that
        if (! is_null(self::$config)) {
            return self::$config;
        }

        // merge all config sources together config_definitions.json > db config > config.php
        self::loadDefaults();
        self::loadDB();
        self::loadUserConfigFile(self::$config);

        // final cleanups and validations
        self::processConfig();

        // set to global for legacy/external things (is this needed?)
        global $config;
        $config = self::$config;

        return self::$config;
    }

    /**
     * Reload the config from files/db
     * @return mixed
     */
    public static function reload()
    {
        self::$config = null;

        return self::load();
    }

    /**
     * Get the config setting definitions
     *
     * @return array
     */
    public static function getDefinitions()
    {
        return json_decode(file_get_contents(base_path('misc/config_definitions.json')), true)['config'];
    }

    private static function loadDefaults()
    {
        self::$config['install_dir'] = base_path();
        $definitions = self::getDefinitions();

        foreach ($definitions as $path => $def) {
            if (array_key_exists('default', $def)) {
                Arr::set(self::$config, $path, $def['default']);
            }
        }

        // load macros from json
        $macros = json_decode(file_get_contents(base_path('misc/macros.json')), true);
        Arr::set(self::$config, 'alert.macros.rule', $macros);

        self::processDefaults();
    }

    /**
     * Load the user config from config.php
     * @param array $config (this should be self::$config)
     */
    private static function loadUserConfigFile(&$config)
    {
        // Load user config file
        @include base_path('config.php');
    }

    /**
     * Get a config value, if non existent null (or default if set) will be returned
     *
     * @param string $key period separated config variable name
     * @param mixed $default optional value to return if the setting is not set
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        if (! Str::contains($key, '.')) {
            return $default;
        }

        return Arr::get(self::$config, $key, $default);
    }

    /**
     * Unset a config setting
     * or multiple
     *
     * @param string|array $key
     */
    public static function forget($key)
    {
        Arr::forget(self::$config, $key);
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
     *
     * @param string $os The os name
     * @param string $key period separated config variable name
     * @param mixed $default optional value to return if the setting is not set
     * @return mixed
     */
    public static function getOsSetting($os, $key, $default = null)
    {
        if ($os) {
            \LibreNMS\Util\OS::loadDefinition($os);

            if (isset(self::$config['os'][$os][$key])) {
                return self::$config['os'][$os][$key];
            }

            $os_key = "os.$os.$key";
            if (self::has($os_key)) {
                return self::get($os_key);
            }
        }

        return $default;
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
    public static function getCombined($os, $key, $default = [])
    {
        if (! self::has($key)) {
            return self::getOsSetting($os, $key, $default);
        }

        if (! isset(self::$config['os'][$os][$key])) {
            if (! Str::contains($key, '.')) {
                return self::get($key, $default);
            }
            if (! self::has("os.$os.$key")) {
                return self::get($key, $default);
            }
        }

        return array_unique(array_merge(
            (array) self::get($key, $default),
            (array) self::getOsSetting($os, $key, $default)
        ));
    }

    /**
     * Set a variable in the global config
     *
     * @param mixed $key period separated config variable name
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        Arr::set(self::$config, $key, $value);
    }

    /**
     * Save setting to persistent storage.
     *
     * @param mixed $key period separated config variable name
     * @param mixed $value
     * @return bool if the save was successful
     */
    public static function persist($key, $value)
    {
        try {
            \App\Models\Config::updateOrCreate(['config_name' => $key], [
                'config_name' => $key,
                'config_value' => $value,
            ]);
            Arr::set(self::$config, $key, $value);

            // delete any children (there should not be any unless it is legacy)
            \App\Models\Config::query()->where('config_name', 'like', "$key.%")->delete();

            return true;
        } catch (Exception $e) {
            if (class_exists(Log::class)) {
                Log::error($e);
            }
            if (Debug::isEnabled()) {
                echo $e;
            }

            return false;
        }
    }

    /**
     * Forget a key and all it's descendants from persistent storage.
     * This will effectively set it back to default.
     *
     * @param string $key
     * @return int|false
     */
    public static function erase($key)
    {
        self::forget($key);
        try {
            return \App\Models\Config::withChildren($key)->delete();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if a setting is set
     *
     * @param string $key period separated config variable name
     * @return bool
     */
    public static function has($key)
    {
        if (isset(self::$config[$key])) {
            return true;
        }

        if (! Str::contains($key, '.')) {
            return false;
        }

        return Arr::has(self::$config, $key);
    }

    /**
     * Serialise the whole configuration to json for use in external processes.
     *
     * @return string
     */
    public static function toJson()
    {
        return json_encode(self::$config);
    }

    /**
     * Get the full configuration array
     * @return array
     */
    public static function getAll()
    {
        return self::$config;
    }

    /**
     * merge the database config with the global config
     * Global config overrides db
     */
    private static function loadDB()
    {
        if (! Eloquent::isConnected()) {
            return;
        }

        try {
            \App\Models\Config::get(['config_name', 'config_value'])
                ->each(function ($item) {
                    Arr::set(self::$config, $item->config_name, $item->config_value);
                });
        } catch (QueryException $e) {
            // possibly table config doesn't exist yet
        }

        // load graph types from the database
        self::loadGraphsFromDb(self::$config);
    }

    private static function loadGraphsFromDb(&$config)
    {
        try {
            $graph_types = GraphType::all()->toArray();
        } catch (QueryException $e) {
            // possibly table config doesn't exist yet
            $graph_types = [];
        }

        // load graph types from the database
        foreach ($graph_types as $graph) {
            $g = [];
            foreach ($graph as $k => $v) {
                if (strpos($k, 'graph_') == 0) {
                    // remove leading 'graph_' from column name
                    $key = str_replace('graph_', '', $k);
                } else {
                    $key = $k;
                }
                $g[$key] = $v;
            }

            $config['graph_types'][$g['type']][$g['subtype']] = $g;
        }
    }

    /**
     * Handle defaults that are set programmatically
     */
    private static function processDefaults()
    {
        Arr::set(self::$config, 'log_dir', base_path('logs'));
        Arr::set(self::$config, 'distributed_poller_name', php_uname('n'));

        // set base_url from access URL
        if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['SERVER_PORT'])) {
            $port = $_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '';
            // handle literal IPv6
            $server = Str::contains($_SERVER['SERVER_NAME'], ':') ? "[{$_SERVER['SERVER_NAME']}]" : $_SERVER['SERVER_NAME'];
            Arr::set(self::$config, 'base_url', "http://$server$port/");
        }

        // graph color copying
        Arr::set(self::$config, 'graph_colours.mega', array_merge(
            (array) Arr::get(self::$config, 'graph_colours.psychedelic', []),
            (array) Arr::get(self::$config, 'graph_colours.manycolours', []),
            (array) Arr::get(self::$config, 'graph_colours.default', []),
            (array) Arr::get(self::$config, 'graph_colours.mixed', [])
        ));
    }

    /**
     * Process the config after it has been loaded.
     * Make sure certain variables have been set properly and
     */
    private static function processConfig()
    {
        // If we're on SSL, let's properly detect it
        if (
            isset($_SERVER['HTTPS']) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        ) {
            self::set('base_url', preg_replace('/^http:/', 'https:', self::get('base_url')));
        }

        self::set('base_url', Str::finish(self::get('base_url'), '/'));

        if (! self::get('email_from')) {
            self::set('email_from', '"' . self::get('project_name') . '" <' . self::get('email_user') . '@' . php_uname('n') . '>');
        }

        // Define some variables if they aren't set by user definition in config_definitions.json
        self::set('applied_site_style', self::get('site_style'));
        self::setDefault('html_dir', '%s/html', ['install_dir']);
        self::setDefault('rrd_dir', '%s/rrd', ['install_dir']);
        self::setDefault('mib_dir', '%s/mibs', ['install_dir']);
        self::setDefault('log_dir', '%s/logs', ['install_dir']);
        self::setDefault('log_file', '%s/%s.log', ['log_dir', 'project_id']);
        self::setDefault('plugin_dir', '%s/plugins', ['html_dir']);
        self::setDefault('temp_dir', sys_get_temp_dir() ?: '/tmp');
        self::setDefault('irc_nick', '%s', ['project_name']);
        self::setDefault('irc_chan.0', '##%s', ['project_id']);
        self::setDefault('page_title_suffix', '%s', ['project_name']);
//        self::setDefault('email_from', '"%s" <%s@' . php_uname('n') . '>', ['project_name', 'email_user']);  // FIXME email_from set because alerting config

        // deprecated variables
        self::deprecatedVariable('rrdgraph_real_95th', 'rrdgraph_real_percentile');
        self::deprecatedVariable('fping_options.millisec', 'fping_options.interval');
        self::deprecatedVariable('discovery_modules.cisco-vrf', 'discovery_modules.vrf');
        self::deprecatedVariable('discovery_modules.toner', 'discovery_modules.printer-supplies');
        self::deprecatedVariable('poller_modules.toner', 'poller_modules.printer-supplies');
        self::deprecatedVariable('discovery_modules.cisco-sla', 'discovery_modules.slas');
        self::deprecatedVariable('poller_modules.cisco-sla', 'poller_modules.slas');
        self::deprecatedVariable('oxidized.group', 'oxidized.maps.group');

        $persist = Eloquent::isConnected();
        // make sure we have full path to binaries in case PATH isn't set
        foreach (['fping', 'fping6', 'snmpgetnext', 'rrdtool', 'traceroute', 'traceroute6'] as $bin) {
            if (! is_executable(self::get($bin))) {
                if ($persist) {
                    self::persist($bin, self::locateBinary($bin));
                } else {
                    self::set($bin, self::locateBinary($bin));
                }
            }
        }

        self::populateTime();

        // populate legacy DB credentials, just in case something external uses them.  Maybe remove this later
        self::populateLegacyDbCredentials();
    }

    /**
     * Set default values for defaults that depend on other settings, if they are not already loaded
     *
     * @param string $key
     * @param string $value value to set to key or vsprintf() format string for values below
     * @param array $format_values array of keys to send to vsprintf()
     */
    private static function setDefault($key, $value, $format_values = [])
    {
        if (! self::has($key)) {
            if (is_string($value)) {
                $format_values = array_map('self::get', $format_values);
                self::set($key, vsprintf($value, $format_values));
            } else {
                self::set($key, $value);
            }
        }
    }

    /**
     * Copy data from old variables to new ones.
     *
     * @param string $old
     * @param string $new
     */
    private static function deprecatedVariable($old, $new)
    {
        if (self::has($old)) {
            if (Debug::isEnabled()) {
                echo "Copied deprecated config $old to $new\n";
            }
            self::set($new, self::get($old));
        }
    }

    /**
     * Locate the actual path of a binary
     *
     * @param string $binary
     * @return mixed
     */
    public static function locateBinary($binary)
    {
        if (! Str::contains($binary, '/')) {
            $output = `whereis -b $binary`;
            $list = trim(substr($output, strpos($output, ':') + 1));
            $targets = explode(' ', $list);
            foreach ($targets as $target) {
                if (is_executable($target)) {
                    return $target;
                }
            }
        }

        return $binary;
    }

    private static function populateTime()
    {
        $now = time();
        $now -= $now % 300;
        self::set('time.now', $now);
        self::set('time.onehour', $now - 3600); // time() - (1 * 60 * 60);
        self::set('time.fourhour', $now - 14400); // time() - (4 * 60 * 60);
        self::set('time.sixhour', $now - 21600); // time() - (6 * 60 * 60);
        self::set('time.twelvehour', $now - 43200); // time() - (12 * 60 * 60);
        self::set('time.day', $now - 86400); // time() - (24 * 60 * 60);
        self::set('time.twoday', $now - 172800); // time() - (2 * 24 * 60 * 60);
        self::set('time.week', $now - 604800); // time() - (7 * 24 * 60 * 60);
        self::set('time.twoweek', $now - 1209600); // time() - (2 * 7 * 24 * 60 * 60);
        self::set('time.month', $now - 2678400); // time() - (31 * 24 * 60 * 60);
        self::set('time.twomonth', $now - 5356800); // time() - (2 * 31 * 24 * 60 * 60);
        self::set('time.threemonth', $now - 8035200); // time() - (3 * 31 * 24 * 60 * 60);
        self::set('time.sixmonth', $now - 16070400); // time() - (6 * 31 * 24 * 60 * 60);
        self::set('time.year', $now - 31536000); // time() - (365 * 24 * 60 * 60);
        self::set('time.twoyear', $now - 63072000); // time() - (2 * 365 * 24 * 60 * 60);
    }

    public static function populateLegacyDbCredentials()
    {
        $db = config('database.default');

        self::set('db_host', config("database.connections.$db.host", 'localhost'));
        self::set('db_name', config("database.connections.$db.database", 'librenms'));
        self::set('db_user', config("database.connections.$db.username", 'librenms'));
        self::set('db_pass', config("database.connections.$db.password"));
        self::set('db_port', config("database.connections.$db.port", 3306));
        self::set('db_socket', config("database.connections.$db.unix_socket"));
    }
}
