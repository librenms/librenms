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
 * @param array $modules
 */
//function librenms_init($init_modules = array())
//{
//global $console_color, $config;

$install_dir = realpath(__DIR__ . '/..');
$config['install_dir'] = $install_dir;
chdir($install_dir);

// Libraries
require('Net/IPv4.php');
require('Net/IPv6.php');

// initialize the class loader and add custom mappings
require $install_dir . '/LibreNMS/ClassLoader.php';
$classLoader = new LibreNMS\ClassLoader();
$classLoader->registerClass('Console_Color2', $config['install_dir'] . '/lib/console_colour.php');
$classLoader->registerClass('Console_Table', $config['install_dir'] . '/lib/console_table.php');
$classLoader->registerClass('PHPMailer', $config['install_dir'] . "/lib/phpmailer/class.phpmailer.php");
$classLoader->registerClass('SMTP', $config['install_dir'] . "/lib/phpmailer/class.smtp.php");
$classLoader->registerClass('PasswordHash', $config['install_dir'] . '/html/lib/PasswordHash.php');
//    $classLoader->registerDir($install_dir . '/tests', 'LibreNMS\Tests');
$classLoader->register();
if (version_compare(PHP_VERSION, '5.4', '>=')) {
    require $install_dir . '/lib/influxdb-php/vendor/autoload.php';
}
require $install_dir . '/lib/yaml/vendor/autoload.php';

// function only files
require_once $install_dir . '/includes/common.php';
require $install_dir . '/includes/dbFacile.php';
require $install_dir . '/includes/rrdtool.inc.php';
require $install_dir . '/includes/influxdb.inc.php';
require $install_dir . '/includes/datastore.inc.php';
require $install_dir . '/includes/billing.php';
require $install_dir . '/includes/syslog.php';
require $install_dir . '/includes/snmp.inc.php';
require $install_dir . '/includes/services.inc.php';
require $install_dir . '/includes/mergecnf.inc.php';
require $install_dir . '/includes/functions.php';
require $install_dir . '/includes/rewrites.php';  // FIXME both definitions and functions
require $install_dir . '/lib/htmlpurifier-4.8.0-lite/library/HTMLPurifier.auto.php';

if (module_selected('web', $init_modules)) {
    chdir($install_dir . '/html');
    require $install_dir . '/html/includes/functions.inc.php';
}

if (module_selected('discovery', $init_modules)) {
    require $install_dir . '/includes/discovery/functions.inc.php';
}

if (module_selected('polling', $init_modules)) {
    require_once $install_dir . '/includes/device-groups.inc.php';
    require $install_dir . '/includes/polling/functions.inc.php';
}

if (module_selected('alerts', $init_modules)) {
    require_once $install_dir . '/includes/device-groups.inc.php';
    require $install_dir . '/includes/alerts.inc.php';
}


// variable definitions
require $install_dir . '/includes/cisco-entities.php';
require $install_dir . '/includes/vmware_guestid.inc.php';
require $install_dir . '/includes/defaults.inc.php';
require $install_dir . '/includes/definitions.inc.php';
include $install_dir . '/config.php';

// init memcached
if ($config['memcached']['enable'] === true) {
    if (class_exists('Memcached')) {
        $config['memcached']['ttl'] = 60;
        $config['memcached']['resource'] = new Memcached();
        $config['memcached']['resource']->addServer($config['memcached']['host'], $config['memcached']['port']);
    } else {
        echo "WARNING: You have enabled memcached but have not installed the PHP bindings. Disabling memcached support.\n";
        echo "Try 'apt-get install php5-memcached' or 'pecl install memcached'. You will need the php5-dev and libmemcached-dev packages to use pecl.\n\n";
        $config['memcached']['enable'] = 0;
    }
}

if (!module_selected('nodb', $init_modules)) {
    // Connect to database
    $database_link = mysqli_connect('p:' . $config['db_host'], $config['db_user'], $config['db_pass']);
    if (!$database_link) {
        echo '<h2>MySQL Error</h2>';
        echo mysqli_connect_error();
        die;
    }
    $database_db = mysqli_select_db($database_link, $config['db_name']);

    // pull in the database config settings
    mergedb();

    // load graph types from the database
    require $install_dir . '/includes/load_db_graph_types.inc.php';
}

if (file_exists($config['install_dir'] . '/html/includes/authentication/'.$config['auth_mechanism'].'.inc.php')) {
    require $config['install_dir'] . '/html/includes/authentication/'.$config['auth_mechanism'].'.inc.php';
} else {
    print_error('ERROR: no valid auth_mechanism defined!');
    exit();
}

if (module_selected('web', $init_modules)) {
    umask(0002);
    require $install_dir . '/html/includes/vars.inc.php';
    $tmp_list = dbFetchRows('SELECT DISTINCT(`os`) FROM `devices`');
    $os_list = array();
    foreach ($tmp_list as $k => $v) {
        $os_list[] = $config['install_dir'].'/includes/definitions/'. $v['os'] . '.yaml';
    }
    load_all_os($os_list);
}

$console_color = new Console_Color2();

if (module_selected('auth', $init_modules) ||
    (
        module_selected('graphs', $init_modules) &&
        isset($config['allow_unauth_graphs']) &&
        $config['allow_unauth_graphs'] != true
    )
) {
    require $install_dir . '/html/includes/authenticate.inc.php';
}

function module_selected($module, $modules)
{
    return in_array($module, (array) $modules);
}
