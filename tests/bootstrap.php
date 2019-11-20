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

if (getenv('DBTEST')) {
    global $migrate_result, $migrate_output;

    // create testing table if needed
    $db_config = \config("database.connections.testing");
    $connection = new PDO("mysql:host={$db_config['host']}", $db_config['username'], $db_config['password']);
    $connection->query("CREATE DATABASE IF NOT EXISTS {$db_config['database']} CHARACTER SET utf8 COLLATE utf8_unicode_ci");
    unset($connection); // close connection

    // try to avoid erasing people's primary databases
    if ($db_config['database'] !== \config('database.connections.mysql.database', 'librenms')) {
        echo "Refreshing database...";
        $migrate_result = Artisan::call('migrate:fresh', ['--seed' => true, '--env' => 'testing', '--database' => 'testing']);
        $migrate_output = Artisan::output();
        echo "done\n";
    } else {
        echo "Info: Refusing to reset main database: {$db_config['database']}.  Running migrations.\n";
        $migrate_result = Artisan::call('migrate', ['--seed' => true, '--env' => 'testing', '--database' => 'testing']);
        $migrate_output = Artisan::output();
    }
    unset($db_config);
}

// reload the config including database config
Config::reload();
load_all_os();
