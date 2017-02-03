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

$install_dir = realpath(__DIR__ . '/..');

$init_modules = array('web');

if (!getenv('SNMPSIM')) {
    $init_modules[] = 'mocksnmp';
}

if (getenv('DBTEST')) {
    if (!is_file($install_dir . '/config.php')) {
        exec("cp $install_dir/tests/config/config.test.php $install_dir/config.php");
    }
}

require $install_dir . '/includes/init.php';
chdir($install_dir);

$config['install_dir'] = $install_dir;
$config['mib_dir'] = $install_dir . '/mibs';
$config['snmpget'] = 'snmpget';

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_WARNING);
