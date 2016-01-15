<?php
/*
 * LibreNMS HP MSM OS information module
 *
 * Copyright (c) 2016 Mike Rostermund <mike@kollegienet.dk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

preg_match_all('/^(MSM\d{3})|Serial number ([\S]+)|Firmware version (\d+\.\d+\.\d+\.\d+-\d+)/', $poll_device['sysDescr'], $matches);
$hardware = $matches[1][0];
$serial = $matches[2][1];
$version = $matches[3][2];
