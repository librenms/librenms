<?php
/*
 * LibreNMS Cisco AsyncOS information module
 *
 * Copyright (c) 2017 Mike Williams <mike@mgww.net>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

list($hardware,$version,,$serial) = explode(',', $device['sysDescr']);

preg_match('/\w[\d]+\w?/', $hardware, $regexp_results);
$hardware = $regexp_results[0];

preg_match('/[\d\.-]+/', $version, $regexp_results);
$version = $regexp_results[0];

preg_match('/[[\w]+-[\w]+/', $serial, $regexp_results);
$serial = $regexp_results[0];
