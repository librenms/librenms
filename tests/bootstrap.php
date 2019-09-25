<?php
/**
 * bootstrap.php
 *
 * Initialize the Autoloader and includes for phpunit to be able to run tests
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

use LibreNMS\Config;
use LibreNMS\DB\Eloquent;
use LibreNMS\Exceptions\DatabaseConnectException;
use LibreNMS\Util\Snmpsim;

$install_dir = realpath(__DIR__ . '/..');

$init_modules = array('web', 'discovery', 'polling', 'nodb');

if (!getenv('SNMPSIM')) {
    $init_modules[] = 'mocksnmp';
}

require $install_dir . '/includes/init.php';
chdir($install_dir);

ini_set('display_errors', 1);
//error_reporting(E_ALL & ~E_WARNING);

update_os_cache(true); // Force update of OS Cache

$snmpsim = new Snmpsim('127.1.6.2', 1162, null);
if (getenv('SNMPSIM')) {
    $snmpsim->fork();

    // make PHP hold on a reference to $snmpsim so it doesn't get destructed
    register_shutdown_function(function (Snmpsim $ss) {
        $ss->stop();
    }, $snmpsim);
}

$db_name = 'librenms';
if (getenv('DBTEST')) {
    global $schema, $sql_mode;

    // create testing table if needed
    $db_config = Config::getDatabaseSettings();
    $db_name = $db_config['db_name'];

    $connection = new PDO("mysql:host={$db_config['db_host']}", $db_config['db_user'], $db_config['db_pass']);
    $connection->query("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8 COLLATE utf8_unicode_ci");
    unset($connection); // close connection

    Eloquent::boot();
    Eloquent::setStrictMode();

    $empty_db = (dbFetchCell("SELECT count(*) FROM `information_schema`.`tables` WHERE `table_type` = 'BASE TABLE' AND `table_schema` = ?", [$db_name]) == 0);
}

// reload the config including database config
Config::load();
load_all_os();

if (getenv('DBTEST')) {
    // try to avoid erasing people's primary databases
    if ($db_name !== \config('database.connections.mysql.database', 'librenms')) {
        Artisan::call('migrate:fresh', ['--seed', '--env=testing', '--database=testing']);
    }
}


