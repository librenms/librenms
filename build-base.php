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
    $init_modules = array('nodb');
    require __DIR__ . '/includes/init.php';

    $opts = getopt('ldh:u:p:n:t:s:');

    if (isset($opts['h'])) {
        dbConnect(
            isset($opts['h']) ? $opts['h'] : null,
            isset($opts['u']) ? $opts['u'] : '',
            isset($opts['p']) ? $opts['p'] : '',
            isset($opts['n']) ? $opts['n'] : '',
            isset($opts['t']) ? $opts['t'] : null,
            isset($opts['s']) ? $opts['s'] : null
        );
    } else {
        // use configured database credentials
        \LibreNMS\DB\Eloquent::boot();
    }

    set_debug(isset($opts['d']));
    $skip_schema_lock = isset($opts['l']);
}

require __DIR__ . '/includes/sql-schema/update.php';

exit($return);
