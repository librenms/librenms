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

header("Content-type: text/plain");
header('X-Accel-Buffering: no');

$db_vars = array(
    'dbhost' => 'h',
    'dbuser' => 'u',
    'dbpass' => 'p',
    'dbname' => 'n',
    'dbport' => 't',
    'dbsocket' => 's',
);

$cmd = $config['install_dir'] . '/build-base.php -l';

foreach ($db_vars as $var => $opt) {
    if ($_SESSION[$var]) {
        $cmd .= " -$opt {$_SESSION[$var]}";
    }
}

echo "Starting Update...\n";

if (($fp = popen($cmd . ' 2>&1', "r"))) {
    while (!feof($fp)) {
        $line = stream_get_line($fp, 1024, "\n");
        echo preg_replace('/\033\[[\d;]+m/', '', $line) . PHP_EOL;
        ob_flush();
        flush(); // you have to flush the buffer
    }

    if (pclose($fp) === 0) {
        echo "Database is up to date!";
        $_SESSION['build-ok'] = true;
    }
}

session_write_close();
