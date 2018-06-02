<?php
/*
 * LibreNMS module to capture NTP statistics
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * This module will display NTP details from various device types.
 * To display, modules must store data in for following format:
 * Array
 * (
 *     [UID] => 9093
 *     [peer] => 10.0.99.66
 *     [port] => 123
 *     [stratum] => 4
 *     [peerref] => 131.242.253.96
 *     [label] => 10.0.99.66:123
 *     [status] => 0
 *     [error] =>
 * )
 */

use LibreNMS\Config;

if (file_exists(Config::get('install_dir') . "/includes/discovery/ntp/{$device['os_group']}.inc.php")) {
    include Config::get('install_dir') . "/includes/discovery/ntp/{$device['os_group']}.inc.php";
}

if ($device['os'] == 'awplus') {
    include 'includes/discovery/ntp/awplus.inc.php';
}
