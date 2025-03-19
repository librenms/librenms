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
 *
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$install_dir = realpath(__DIR__ . '/..');

$init_modules = ['web', 'discovery', 'polling', 'nodb'];

require $install_dir . '/includes/init.php';
chdir($install_dir);

ini_set('display_errors', '1');
//error_reporting(E_ALL & ~E_WARNING);

if (getenv('DBTEST')) {
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

    // migrate testing database to make sure it is up-to-date
    Artisan::call('migrate', ['--seed' => true, '--env' => 'testing', '--database' => 'testing']);
    Artisan::output();
}

LibrenmsConfig::invalidateAndReload();

app()->terminate(); // destroy the bootstrap Laravel application
