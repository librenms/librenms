<?php
/**
 * init.php
 *
 * Load includes and initialize needed things
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

/**
 * @param array $modules Which modules to initialize
 */

use LibreNMS\Authentication\LegacyAuth;
use LibreNMS\Config;

global $config, $permissions, $vars, $console_color;

error_reporting(E_ERROR|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

# composer autoload
if (!is_file($install_dir . '/vendor/autoload.php')) {
    require_once $install_dir . '/includes/common.php';
    c_echo("%RError: Missing dependencies%n, run: %B./scripts/composer_wrapper.php install --no-dev%n\n\n");
}
require_once $install_dir . '/vendor/autoload.php';

if (!function_exists('module_selected')) {
    function module_selected($module, $modules)
    {
        return in_array($module, (array) $modules);
    }
}

// function only files
require_once $install_dir . '/includes/common.php';
require_once $install_dir . '/includes/dbFacile.php';
require_once $install_dir . '/includes/rrdtool.inc.php';
require_once $install_dir . '/includes/influxdb.inc.php';
require_once $install_dir . '/includes/prometheus.inc.php';
require_once $install_dir . '/includes/opentsdb.inc.php';
require_once $install_dir . '/includes/graphite.inc.php';
require_once $install_dir . '/includes/datastore.inc.php';
require_once $install_dir . '/includes/billing.php';
require_once $install_dir . '/includes/syslog.php';
if (module_selected('mocksnmp', $init_modules)) {
    require_once $install_dir . '/tests/mocks/mock.snmp.inc.php';
} else {
    require_once $install_dir . '/includes/snmp.inc.php';
}
require_once $install_dir . '/includes/services.inc.php';
require_once $install_dir . '/includes/functions.php';
require_once $install_dir . '/includes/rewrites.php';

if (module_selected('web', $init_modules)) {
    chdir($install_dir . '/html');
    require_once $install_dir . '/html/includes/functions.inc.php';
}

if (module_selected('discovery', $init_modules)) {
    require_once $install_dir . '/includes/discovery/functions.inc.php';
}

if (module_selected('polling', $init_modules)) {
    require_once $install_dir . '/includes/device-groups.inc.php';
    require_once $install_dir . '/includes/polling/functions.inc.php';
}

if (module_selected('alerts', $init_modules)) {
    require_once $install_dir . '/includes/device-groups.inc.php';
    require_once $install_dir . '/includes/alerts.inc.php';
}

if (module_selected('laravel', $init_modules)) {
    \LibreNMS\Util\Laravel::bootCli();
}

if (!module_selected('nodb', $init_modules)) {
    \LibreNMS\DB\Eloquent::boot();

    if (!\LibreNMS\DB\Eloquent::isConnected()) {
        echo "Could not connect to database, check logs/librenms.log.\n";
        exit;
    }
}

// Display config.php errors instead of http 500
$display_bak = ini_get('display_errors');
ini_set('display_errors', 1);

// Load config if not already loaded (which is the case if inside Laravel)
if (!Config::has('install_dir')) {
    Config::load();
}

// set display_errors back
ini_set('display_errors', $display_bak);


if (isset($config['php_memory_limit']) && is_numeric($config['php_memory_limit']) && $config['php_memory_limit'] > 128) {
    ini_set('memory_limit', $config['php_memory_limit'].'M');
}

try {
    LegacyAuth::get();
} catch (Exception $exception) {
    print_error('ERROR: no valid auth_mechanism defined!');
    echo $exception->getMessage() . PHP_EOL;
    exit();
}

if (module_selected('discovery', $init_modules) && !update_os_cache()) {
    // load_all_os() is called by update_os_cache() if updated, no need to call twice
    load_all_os();
} elseif (module_selected('web', $init_modules)) {
    load_all_os(!module_selected('nodb', $init_modules));
}

if (module_selected('web', $init_modules)) {
    umask(0002);
    if (!isset($config['title_image'])) {
        $config['title_image'] = 'images/librenms_logo_'.$config['site_style'].'.svg';
    }
    require $install_dir . '/html/includes/vars.inc.php';
}

$console_color = new Console_Color2();

if (module_selected('auth', $init_modules) ||
    (
        module_selected('graphs', $init_modules) &&
        isset($config['allow_unauth_graphs']) &&
        $config['allow_unauth_graphs'] != true
    )
) {
    // populate the permissions cache TODO: remove?
    $permissions = [];

    $user_id = LegacyAuth::id();
    foreach (dbFetchColumn('SELECT device_id FROM devices_perms WHERE user_id=?', [$user_id]) as $device_id) {
        $permissions['device'][$device_id] = 1;
    }

    foreach (dbFetchColumn('SELECT port_id FROM ports_perms WHERE user_id=?', [$user_id]) as $port_id) {
        $permissions['port'][$port_id] = 1;
    }

    foreach (dbFetchColumn('SELECT bill_id FROM bill_perms WHERE user_id=?', [$user_id]) as $bill_id) {
        $permissions['bill'][$bill_id] = 1;
    }
    unset($user_id, $device_id, $port_id, $bill_id);
}
