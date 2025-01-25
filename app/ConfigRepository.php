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

namespace App;

use App\Models\Callback;
use App\Models\GraphType;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LibreNMS\DB\Eloquent;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Version;
use Log;
use Symfony\Component\Yaml\Yaml;

class ConfigRepository
{
    private array $config;

    /**
     * Load the config, if the database connected, pull in database settings.
     *
     * return &array
     */
    public function __construct()
    {
        // load config settings that can be cached
        $cache_ttl = config('librenms.config_cache_ttl');
        $this->config = Cache::driver($cache_ttl == 0 ? 'null' : 'file')->remember('librenms-config', $cache_ttl, function () {
            $this->config = [];
            // merge all config sources together config_definitions.json & os defs > db config > config.php
            $this->loadPreUserConfigDefaults();
            $this->loadAllOsDefinitions();
            $this->loadDB();
            $this->loadUserConfigFile($this->config);
            $this->loadPostUserConfigDefaults();

            return $this->config;
        });

        // set config settings that must change every run
        $this->loadRuntimeSettings();
    }

    /**
     * Get the config setting definitions
     *
     * @return array
     */
    public function getDefinitions(): array
    {
        return json_decode(file_get_contents($this->get('install_dir') . '/misc/config_definitions.json'), true)['config'];
    }

    /**
     * Load the user config from config.php
     *
     * @param  array  $config  (this should be $this->config)
     */
    private function loadUserConfigFile(&$config): void
    {
        // Load user config file
        $file = $this->get('install_dir') . '/config.php';
        if (is_file($file)) {
            @include $file;
        }
    }

    /**
     * Get a config value, if non existent null (or default if set) will be returned
     *
     * @param  string  $key  period separated config variable name
     * @param  mixed  $default  optional value to return if the setting is not set
     * @return mixed
     */
    public function get($key, $default = null): mixed
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }

        if (! Str::contains($key, '.')) {
            return $default;
        }

        return Arr::get($this->config, $key, $default);
    }

    /**
     * Unset a config setting
     * or multiple
     *
     * @param  string|array  $key
     */
    public function forget($key): void
    {
        Arr::forget($this->config, $key);
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
    public function getDeviceSetting($device, $key, $global_prefix = null, $default = null): mixed
    {
        if (isset($device[$key])) {
            return $device[$key];
        }

        if (isset($global_prefix)) {
            $key = "$global_prefix.$key";
        }

        return $this->get($key, $default);
    }

    /**
     * Get a setting from the $config['os'] array using the os of the given device
     *
     * @param  string  $os  The os name
     * @param  string  $key  period separated config variable name
     * @param  mixed  $default  optional value to return if the setting is not set
     * @return mixed
     */
    public function getOsSetting($os, $key, $default = null): mixed
    {
        if ($os) {
            return $this->get("os.$os.$key", $default);
        }

        return $default;
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
    public function getCombined(?string $os, string $key, string $global_prefix = '', array $default = []): array
    {
        $global_key = $global_prefix . $key;
        $os_key = "os.$os.$key";

        if (! $this->has($os_key)) {
            return (array) $this->get($global_key, $default);
        }

        if (! $this->has($global_key)) {
            return (array) $this->getOsSetting($os, $key, $default);
        }

        return array_unique(array_merge(
            (array) $this->get($global_key),
            (array) $this->get($os_key)
        ));
    }

    /**
     * Set a variable in the global config
     *
     * @param  mixed  $key  period separated config variable name
     * @param  mixed  $value
     */
    public function set($key, $value): void
    {
        Arr::set($this->config, $key, $value);
    }

    /**
     * Save setting to persistent storage.
     *
     * @param  mixed  $key  period separated config variable name
     * @param  mixed  $value
     * @return bool if the save was successful
     */
    public function persist($key, $value): bool
    {
        try {
            Arr::set($this->config, $key, $value);

            if (! Eloquent::isConnected()) {
                return false;  // can't save it if there is no DB
            }

            \App\Models\Config::updateOrCreate(['config_name' => $key], [
                'config_name' => $key,
                'config_value' => $value,
            ]);

            // delete any children (there should not be any unless it is legacy)
            \App\Models\Config::query()->where('config_name', 'like', "$key.%")->delete();

            $this->invalidateCache(); // config has been changed, it will need to be reloaded

            return true;
        } catch (Exception $e) {
            if (class_exists(Log::class)) {
                Log::error($e);
            }
            if (Debug::isEnabled()) {
                echo $e;
            }

            if ($e instanceof \Illuminate\Database\QueryException && $e->getCode() !== '42S02') {
                // re-throw, else Config service provider get stuck in a loop
                // if there is an error (database not connected)
                // unless it is table not found (migrations have not been run yet)

                throw $e;
            }

            return false;
        }
    }

    /**
     * Forget a key and all it's descendants from persistent storage.
     * This will effectively set it back to default.
     *
     * @param  string  $key
     * @return int|false
     */
    public function erase($key): bool|int
    {
        $this->forget($key);
        try {
            return \App\Models\Config::withChildren($key)->delete();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if a setting is set
     *
     * @param  string  $key  period separated config variable name
     * @return bool
     */
    public function has($key): bool
    {
        if (isset($this->config[$key])) {
            return true;
        }

        if (! Str::contains($key, '.')) {
            return false;
        }

        return Arr::has($this->config, $key);
    }

    /**
     * Serialise the whole configuration to json for use in external processes.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->config);
    }

    /**
     * Get the full configuration array
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->config;
    }

    /**
     * Invalidate config cache (but don't reload)
     * Next time the config is loaded, it will loaded fresh
     * Because this is currently hardcoded to file cache, it will only clear the cache on this node
     */
    public function invalidateCache(): void
    {
        Cache::driver('file')->forget('librenms-config');
    }

    /**
     * merge the database config with the global config,
     * global config overrides db
     */
    private function loadDB(): void
    {
        if (! Eloquent::isConnected()) {
            return;  // don't even try if no DB
        }

        try {
            \App\Models\Config::get(['config_name', 'config_value'])
                ->each(function ($item) {
                    Arr::set($this->config, $item->config_name, $item->config_value);
                });
        } catch (QueryException $e) {
            // possibly table config doesn't exist yet
        }

        // load graph types from the database
        $this->loadGraphsFromDb($this->config);
    }

    private function loadGraphsFromDb(&$config): void
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
    private function loadPreUserConfigDefaults(): void
    {
        $this->config['install_dir'] = realpath(__DIR__ . '/..');
        $definitions = $this->getDefinitions();

        foreach ($definitions as $path => $def) {
            if (array_key_exists('default', $def)) {
                Arr::set($this->config, $path, $def['default']);
            }
        }

        // load macros from json
        $macros = json_decode(file_get_contents($this->get('install_dir') . '/misc/macros.json'), true);
        Arr::set($this->config, 'alert.macros.rule', $macros);

        Arr::set($this->config, 'log_dir', $this->get('install_dir') . '/logs');
        Arr::set($this->config, 'distributed_poller_name', php_uname('n'));

        // set base_url from access URL
        if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['SERVER_PORT'])) {
            $port = $_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '';
            // handle literal IPv6
            $server = Str::contains($_SERVER['SERVER_NAME'], ':') ? "[{$_SERVER['SERVER_NAME']}]" : $_SERVER['SERVER_NAME'];
            Arr::set($this->config, 'base_url', "http://$server$port/");
        }

        // graph color copying
        Arr::set($this->config, 'graph_colours.mega', array_merge(
            (array) Arr::get($this->config, 'graph_colours.psychedelic', []),
            (array) Arr::get($this->config, 'graph_colours.manycolours', []),
            (array) Arr::get($this->config, 'graph_colours.default', []),
            (array) Arr::get($this->config, 'graph_colours.mixed', [])
        ));
    }

    private function loadPostUserConfigDefaults(): void
    {
        if (! $this->get('email_from')) {
            $this->set('email_from', '"' . $this->get('project_name') . '" <' . $this->get('email_user') . '@' . php_uname('n') . '>');
        }
        // Define some variables if they aren't set by user definition in config_definitions.json
        $this->setDefault('html_dir', '%s/html', ['install_dir']);
        $this->setDefault('rrd_dir', '%s/rrd', ['install_dir']);
        $this->setDefault('mib_dir', '%s/mibs', ['install_dir']);
        $this->setDefault('log_dir', '%s/logs', ['install_dir']);
        $this->setDefault('log_file', '%s/%s.log', ['log_dir', 'project_id']);
        $this->setDefault('plugin_dir', '%s/plugins', ['html_dir']);
        $this->setDefault('temp_dir', sys_get_temp_dir() ?: '/tmp');
        $this->setDefault('irc_nick', '%s', ['project_name']);
        $this->setDefault('irc_chan.0', '##%s', ['project_id']);
        $this->setDefault('page_title_suffix', '%s', ['project_name']);
//        $this->setDefault('email_from', '"%s" <%s@' . php_uname('n') . '>', ['project_name', 'email_user']);  // FIXME email_from set because alerting config

        // deprecated variables
        $this->deprecatedVariable('rrdgraph_real_95th', 'rrdgraph_real_percentile');
        $this->deprecatedVariable('fping_options.millisec', 'fping_options.interval');
        $this->deprecatedVariable('discovery_modules.cisco-vrf', 'discovery_modules.vrf');
        $this->deprecatedVariable('discovery_modules.toner', 'discovery_modules.printer-supplies');
        $this->deprecatedVariable('poller_modules.toner', 'poller_modules.printer-supplies');
        $this->deprecatedVariable('discovery_modules.cisco-sla', 'discovery_modules.slas');
        $this->deprecatedVariable('poller_modules.cisco-sla', 'poller_modules.slas');
        $this->deprecatedVariable('oxidized.group', 'oxidized.maps.group');

        // migrate device display
        if (! $this->has('device_display_default')) {
            $display_value = '{{ $hostname }}';
            if ($this->get('force_hostname_to_sysname')) {
                $display_value = '{{ $sysName }}';
            } elseif ($this->get('force_ip_to_sysname')) {
                $display_value = '{{ $sysName_fallback }}';
            }

            $this->persist('device_display_default', $display_value);
        }

        // make sure we have full path to binaries in case PATH isn't set
        foreach (['fping', 'fping6', 'snmpgetnext', 'rrdtool', 'traceroute'] as $bin) {
            if (! is_executable($this->get($bin))) {
                $this->persist($bin, $this->locateBinary($bin));
            }
        }

        if (! $this->has('rrdtool_version')) {
            $this->persist('rrdtool_version', (new Version($this))->rrdtool());
        }
        if (! $this->has('snmp.unescape')) {
            $this->persist('snmp.unescape', version_compare((new Version($this))->netSnmp(), '5.8.0', '<'));
        }
        if (! $this->has('reporting.usage')) {
            $this->persist('reporting.usage', (bool) Callback::get('enabled'));
        }

        // populate legacy DB credentials, just in case something external uses them.  Maybe remove this later
        $this->populateLegacyDbCredentials();
    }

    private function loadRuntimeSettings(): void
    {
        // If we're on SSL, let's properly detect it
        if (
            isset($_SERVER['HTTPS']) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        ) {
            $this->set('base_url', preg_replace('/^http:/', 'https:', $this->get('base_url', '')));
        }
        $this->set('base_url', Str::finish($this->get('base_url', ''), '/'));

        $this->set('applied_site_style', $this->get('site_style'));

        $this->populateTime();
    }

    /**
     * Set default values for defaults that depend on other settings, if they are not already loaded
     *
     * @param  string  $key
     * @param  string  $value  value to set to key or vsprintf() format string for values below
     * @param  array  $format_values  array of keys to send to vsprintf()
     */
    private function setDefault($key, $value, $format_values = []): void
    {
        if (! $this->has($key)) {
            if (is_string($value)) {
                $format_values = array_map([$this, 'get'], $format_values);
                $this->set($key, vsprintf($value, $format_values));
            } else {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Copy data from old variables to new ones.
     *
     * @param  string  $old
     * @param  string  $new
     */
    private function deprecatedVariable($old, $new): void
    {
        if ($this->has($old)) {
            if (Debug::isEnabled()) {
                echo "Copied deprecated config $old to $new\n";
            }
            $this->set($new, $this->get($old));
        }
    }

    /**
     * Locate the actual path of a binary
     *
     * @param  string  $binary
     * @return mixed
     */
    public function locateBinary($binary): mixed
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

    private function populateTime(): void
    {
        $now = time();
        $now -= $now % 300;
        $this->set('time.now', $now);
        $this->set('time.onehour', $now - 3600); // time() - (1 * 60 * 60);
        $this->set('time.fourhour', $now - 14400); // time() - (4 * 60 * 60);
        $this->set('time.sixhour', $now - 21600); // time() - (6 * 60 * 60);
        $this->set('time.twelvehour', $now - 43200); // time() - (12 * 60 * 60);
        $this->set('time.day', $now - 86400); // time() - (24 * 60 * 60);
        $this->set('time.twoday', $now - 172800); // time() - (2 * 24 * 60 * 60);
        $this->set('time.threeday', $now - 259200); // time() - (3 * 24 * 60 * 60);
        $this->set('time.week', $now - 604800); // time() - (7 * 24 * 60 * 60);
        $this->set('time.tenday', $now - 864000); // time() - (10 * 24 * 60 * 60);
        $this->set('time.twoweek', $now - 1209600); // time() - (2 * 7 * 24 * 60 * 60);
        $this->set('time.month', $now - 2678400); // time() - (31 * 24 * 60 * 60);
        $this->set('time.twomonth', $now - 5356800); // time() - (2 * 31 * 24 * 60 * 60);
        $this->set('time.threemonth', $now - 8035200); // time() - (3 * 31 * 24 * 60 * 60);
        $this->set('time.sixmonth', $now - 16070400); // time() - (6 * 31 * 24 * 60 * 60);
        $this->set('time.year', $now - 31536000); // time() - (365 * 24 * 60 * 60);
        $this->set('time.twoyear', $now - 63072000); // time() - (2 * 365 * 24 * 60 * 60);
    }

    public function populateLegacyDbCredentials(): void
    {
        if (! class_exists('config')) {
            return;
        }

        $db = config('database.default');

        $this->set('db_host', config("database.connections.$db.host", 'localhost'));
        $this->set('db_name', config("database.connections.$db.database", 'librenms'));
        $this->set('db_user', config("database.connections.$db.username", 'librenms'));
        $this->set('db_pass', config("database.connections.$db.password"));
        $this->set('db_port', config("database.connections.$db.port", 3306));
        $this->set('db_socket', config("database.connections.$db.unix_socket"));
    }

    /**
     * Load all OS settings from yaml or cache
     */
    private function loadAllOsDefinitions(): void
    {
        $os_list = glob($this->get('install_dir') . '/includes/definitions/*.yaml');

        foreach ($os_list as $yaml_file) {
            $os = basename($yaml_file, '.yaml');
            $os_def = Yaml::parse(file_get_contents($yaml_file));

            $this->set("os.$os", $os_def);
        }
    }
}
