#!/usr/bin/env php
<?php
/**
 * poller.php
 *
 * -Description-
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
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 * Modified 4/17/19
 * @author Heath Barnhart <hbarnhart@kanren.net>
 */
$init_modules = ['polling', 'alerts', 'laravel'];
require __DIR__ . '/includes/init.php';

$options = getopt('h:rfpdvm:q');

c_echo('%RWarning: poller.php is deprecated!%n Use %9lnms device:poll%n instead.' . PHP_EOL . PHP_EOL);

if (empty($options['h'])) {
    echo "-h <device id> | <device hostname wildcard>  Poll single device\n";
    echo "-h odd             Poll odd numbered devices\n";
    echo "-h even            Poll even numbered devices\n";
    echo "-h all             Poll all devices\n\n";
    echo "Debugging and testing options:\n";
    echo "-r                 Do not create or update RRDs\n";
    echo "-f                 Do not insert data into InfluxDB\n";
    echo "-p                 Do not insert data into Prometheus\n";
    echo "-d                 Enable debugging output\n";
    echo "-v                 Enable verbose debugging output\n";
    echo "-m                 Specify module(s) to be run. Comma separate modules, submodules may be added with /\n";
    echo "-q                 Quiet, minimal output /\n";
    echo "\n";
    echo "No polling type specified!\n";
    exit;
}

$arguments = [
    'device spec' => $options['h'],
    '--verbose' => isset($options['v']) ? 3 : (isset($options['d']) ? 2 : 1),
];

if (isset($options['m'])) {
    $arguments['--modules'] = $options['m'];
}

if (isset($options['q'])) {
    $arguments['--quiet'] = true;
}

if (isset($options['r']) || isset($options['f']) || isset($options['p'])) {
    $arguments['--no-data'] = true;
}

$return = Artisan::call('device:poll', $arguments);

exit($return);
