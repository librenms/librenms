<?php
/**
 * db-update.inc.php
 *
 * Run database update/deploy for installer
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

if (file_exists($config['install_dir'] . '/config.php')) {
    echo("This should only be called during install");
    exit;
}
$init_modules = ['laravel', 'nodb'];
require $config['install_dir'] . '/includes/init.php';

header("Content-type: text/plain");
header('X-Accel-Buffering: no');

$db_vars = array(
    'dbhost' => 'host',
    'dbuser' => 'username',
    'dbpass' => 'password',
    'dbname' => 'database',
    'dbport' => 'port',
    'dbsocket' => 'unix_socket',
);

\Config::set('database.connections.setup', [
    "driver" => "mysql",
    "host" => $_SESSION['dbhost'] ?: 'localhost',
    "port" => $_SESSION['dbhost'] ?: 3306,
    "database" => $_SESSION['dbname'] ?: 'librenms',
    "username" => $_SESSION['dbuser'] ?: 'librenms',
    "password" => $_SESSION['dbpass'] ?: '',
    "charset" => "utf8",
    "collation" => "utf8_unicode_ci",
    "prefix" => "",
    "strict" => true,
    "engine" => null
]);


\Artisan::call('migrate', ['--seed' => true, '--force' => true , '--database' => 'setup']);
$fp = \Artisan::output();
echo "Starting Update...\n";

if ($fp) {
    echo $fp;
}

ob_end_flush();
flush();
session_write_close();
