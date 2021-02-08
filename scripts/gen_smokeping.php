#!/usr/bin/env php
<?php
/**
 * gen_smokeping.php
 *
 * Legacy wrapper for generating smokeping configurations
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
 * @copyright  2020 Adam Bishop
 * @author     Adam Bishop <adam@omega.org.uk>
 */
if (php_sapi_name() === 'cli') {
    $init_modules = [];
    require realpath(__DIR__ . '/..') . '/includes/init.php';

    $return = \Artisan::call('smokeping:generate --targets --no-header --no-dns --single-process --compat');
    echo \Artisan::Output();

    exit($return);
}

exit();
