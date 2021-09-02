<?php

/*
 * LibreNMS OS Polling module for the CradlePoint WiPipe Platform
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// Rip hardware and firmware version from sysDescr string -- example: Cradlepoint CBA850, Firmware Version 6.2.0.dd92f49
preg_match('/(.*) ([\w\d]+), (.*) ([\w\d\.]+)/', $device['sysDescr'], $wipipe_data);
$hardware = $wipipe_data[2];
$version = $wipipe_data[4];
