#!/usr/bin/env php
<?php

/**
 * discovery.php
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
 */
$init_modules = ['discovery', 'alerts', 'laravel'];
require __DIR__ . '/includes/init.php';

$options = getopt('h:m:d::v::q', ['os:', 'type:']);

c_echo('%RWarning: discovery.php is deprecated!%n Use %9lnms device:discover%n instead.' . PHP_EOL . PHP_EOL);

if (empty($options['h'])) {
    echo "-h <device id> | <device hostname wildcard>  Discover single device\n";
    echo "-h odd             Discover odd numbered devices\n";
    echo "-h even            Discover even numbered devices\n";
    echo "-h all             Discover all devices\n";
    echo "-h new             Discover all devices that have not had a discovery run before\n";
    echo "--os <os_name>     Discover devices only with specified operating system\n";
    echo "--type <type>      Discover devices only with specified type\n";
    echo "\n";
    echo "Debugging and testing options:\n";
    echo "-d                 Enable debugging output\n";
    echo "-v                 Enable verbose debugging output\n";
    echo "-m                 Specify single module to be run. Comma separate modules, submodules may be added with /\n";
    echo "\n";
    echo "Invalid arguments!\n";
    exit;
}

$arguments = [
    'device spec' => $options['h'],
    '--verbose' => isset($options['v']) ? 3 : (isset($options['d']) ? 2 : 1),
];

if (isset($options['m'])) {
    $arguments['--modules'] = [$options['m']];
}

if (isset($options['q'])) {
    $arguments['--quiet'] = true;
}

if (isset($options['os'])) {
    $arguments['--os'] = $options['os'];
}

if (isset($options['type'])) {
    $arguments['--type'] = $options['type'];
}

$return = Artisan::call('device:discover', $arguments);

exit($return);
