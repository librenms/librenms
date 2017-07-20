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

global $config;

$install_dir = realpath(__DIR__ . '/..');

$init_modules = array('web');

if (!getenv('SNMPSIM')) {
    $init_modules[] = 'mocksnmp';
}

if (getenv('DBTEST')) {
    if (!is_file($install_dir . '/config.php')) {
        exec("cp $install_dir/tests/config/config.test.php $install_dir/config.php");
    }
} else {
    $init_modules[] = 'nodb';
}

require $install_dir . '/includes/init.php';
chdir($install_dir);

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_WARNING);

load_all_os();  // pre-load OS so we don't keep loading them

if (getenv('DBTEST')) {
    global $schema, $sql_mode;

    $sql_mode = dbFetchCell("SELECT @@global.sql_mode");
    $empty_db = (dbFetchCell("SELECT count(*) FROM `information_schema`.`tables` WHERE `table_type` = 'BASE TABLE' AND `table_schema` = ?", array($config['db_name'])) == 0);
    dbQuery("SET GLOBAL sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");

    $cmd = $config['install_dir'] . '/build-base.php';
    exec($cmd, $schema);

    register_shutdown_function(function () use ($empty_db, $sql_mode) {
        global $config;
        dbConnect();

        // restore sql_mode
        dbQuery("SET GLOBAL sql_mode='$sql_mode'");

        if ($empty_db) {
            dbQuery("DROP DATABASE " . $config['db_name']);
        } elseif (isset($config['test_db_name']) && $config['db_name'] == $config['test_db_name']) {
            // truncate tables
            $tables = dbFetchColumn('SHOW TABLES');

            $excluded = array(
                'alert_templates',
                'config', // not sure about this one
                'dbSchema',
                'graph_types',
                'port_association_mode',
                'widgets',
            );
            $truncate = array_diff($tables, $excluded);

            dbQuery("SET FOREIGN_KEY_CHECKS = 0");
            foreach ($truncate as $table) {
                dbQuery("TRUNCATE TABLE $table");
            }
            dbQuery("SET FOREIGN_KEY_CHECKS = 1");
        }
    });
}
