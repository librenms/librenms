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
    $init_modules = ['nodb', 'laravel'];
    require __DIR__ . '/includes/init.php';

    $opts = getopt('ldh:u:p:n:t:s:');

    // grab the default settings
    $default = config('database.connections.' . config('database.default'), 'mysql');

    $map = [
        'h' => 'host',
        'u' => 'username',
        'p' => 'password',
        'n' => 'database',
        't' => 'port',
        's' => 'unix_socket',
    ];

    // update any settings
    foreach ($map as $opt => $config_key) {
        if (isset($opts[$opt])) {
            $default[$config_key] = $opts[$opt];
        }
    }

    // save to setup
    \Config::set('database.connections.setup', $default);


    set_debug(isset($opts['d']));
    $skip_schema_lock = isset($opts['l']);
}

require __DIR__ . '/includes/sql-schema/update.php';

exit($return);
