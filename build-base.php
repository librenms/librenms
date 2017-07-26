#!/usr/bin/env php
<?php
/**
 * build-base.php
 *
 * Create database structure.
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

if (!isset($init_modules)) {
    $opts = getopt('ldh:u:p:n:t:s:');

    $db_vars = array(
        'db_host' => 'h',
        'db_user' => 'u',
        'db_pass' => 'p',
        'db_name' => 'n',
        'db_port' => 't',
        'db_socket' => 's',
    );

    $config = array();
    foreach ($db_vars as $setting => $opt) {
        if (isset($opts[$opt])) {
            $config[$setting] = $opts[$opt];
        }
    }

    $init_modules = array();
    require __DIR__  . '/includes/init.php';

    $debug = isset($opts['d']);
    $skip_schema_lock = isset($opts['l']);
}

require 'includes/sql-schema/update.php';

exit($return);
