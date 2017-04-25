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

$version = str_replace('"', '', snmp_get($device, "asyncOSAppliances.2.2.3.0", "-OQv", "IRONPORT-SMI"));
$serial = preg_replace('/^[\w\s\.,:-]*Serial\s#:\s/', '', $poll_device['sysDescr']);

if (preg_match('/^Cisco[\w\s]*/', $poll_device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[0];
}
