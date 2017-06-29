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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Alerts Cronjob
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

$init_modules = array('alerts');
require __DIR__ . '/includes/init.php';

$options = getopt('d::');

$alerts_lock = \LibreNMS\FileLock::lockOrDie('alerts');

if (isset($options['d'])) {
    echo "DEBUG!\n";
    $debug = true;
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_reporting', 1);
} else {
    $debug = false;
    // ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 0);
    // ini_set('error_reporting', 0);
}

if (!defined('TEST') && $config['alert']['disable'] != 'true') {
    echo 'Start: '.date('r')."\r\n";
    echo "ClearStaleAlerts():" . PHP_EOL;
    ClearStaleAlerts();
    echo "RunFollowUp():\r\n";
    RunFollowUp();
    echo "RunAlerts():\r\n";
    RunAlerts();
    echo "RunAcks():\r\n";
    RunAcks();
    echo 'End  : '.date('r')."\r\n";
}

$alerts_lock->release();
