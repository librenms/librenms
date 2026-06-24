#!/usr/bin/env php
<?php

/*
 * Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
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
 * Alerts Cronjob
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts

 * Edited 4/1/19
 * Changed to OOP
 * @author: Heath Barnhart <hbarnhart@kanren.net>
 */

$init_modules = ['alerts', 'laravel'];
require __DIR__ . '/includes/init.php';

$options = getopt('fd::');

c_echo('%RWarning: alerts.php is deprecated!%n Use %9lnms alerts:notify%n instead.' . PHP_EOL . PHP_EOL);

$arguments = [];

if (! isset($options['f'])) {
    $arguments['--scheduler'] = 'cron';
}

if (isset($options['d'])) {
    $arguments['-v'] = true;
}

$return = Artisan::call('alerts:notify', $arguments);
echo Artisan::output();
exit($return);
