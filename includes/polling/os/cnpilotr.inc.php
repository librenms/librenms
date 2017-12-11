<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


$version = trim(snmp_get($device, '.1.3.6.1.4.1.41010.1.1.1.0', '-OQv'), '"');
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.41010.1.1.4.0', '-OQv'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.41010.1.1.6.0', '-OQv'), '"');
