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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Config;
use LibreNMS\Util\Snmpsim;

$install_dir = realpath(__DIR__ . '/..');

$init_modules = ['web', 'discovery', 'polling', 'nodb'];

if (! getenv('SNMPSIM')) {
    $init_modules[] = 'mocksnmp';
}

require $install_dir . '/includes/init.php';
chdir($install_dir);

ini_set('display_errors', '1');
//error_reporting(E_ALL & ~E_WARNING);

$snmpsim = new Snmpsim('127.1.6.2', 1162, null);
if (getenv('SNMPSIM')) {
    $snmpsim->fork(6);

    // make PHP hold on a reference to $snmpsim so it doesn't get destructed
    register_shutdown_function(function (Snmpsim $ss) {
        $ss->stop();
    }, $snmpsim);
}

if (getenv('DBTEST')) {
    global $migrate_result, $migrate_output;

    // create testing table if needed
    $db_config = \config('database.connections.testing');
    $connection = new PDO("mysql:host={$db_config['host']};port={$db_config['port']}", $db_config['username'], $db_config['password']);
    $result = $connection->query("CREATE DATABASE IF NOT EXISTS {$db_config['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if ($connection->errorCode() == '42000') {
        echo implode(' ', $connection->errorInfo()) . PHP_EOL;
        echo "Either create database {$db_config['database']} or populate DB_TEST_USERNAME and DB_TEST_PASSWORD in your .env with credentials that can" . PHP_EOL;
        exit(1);
    }
    unset($connection); // close connection

    // sqlite db file
    // $dbFile = fopen(storage_path('testing.sqlite'), 'a+');
    // ftruncate($dbFile, 0);
    // fclose($dbFile);

    // try to avoid erasing people's primary databases
    if ($db_config['database'] !== \config('database.connections.mysql.database', 'librenms')) {
        if (! getenv('SKIP_DB_REFRESH')) {
            echo 'Refreshing database...';
            $migrate_result = Artisan::call('migrate:fresh', ['--seed' => true, '--env' => 'testing', '--database' => 'testing']);
            $migrate_output = Artisan::output();
            echo "done\n";
        }
    } else {
        echo "Info: Refusing to reset main database: {$db_config['database']}.  Running migrations.\n";
        $migrate_result = Artisan::call('migrate', ['--seed' => true, '--env' => 'testing', '--database' => 'testing']);
        $migrate_output = Artisan::output();
    }
    unset($db_config);
}

Config::reload(); // reload the config including database config
\LibreNMS\Util\OS::updateCache(true); // Force update of OS Cache
