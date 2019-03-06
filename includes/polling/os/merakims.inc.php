<?php
/*
 * LibreNMS Meraki MS polling module
 *
 * Copyright (c) 2015 Will Jones <email@willjones.eu>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (empty($hardware)) {
    $hardware = $device['sysDescr'];
}
// ENTITY-MIB::entPhysicalSerialNum
$serial = snmp_getnext($device, '.1.3.6.1.2.1.47.1.1.1.1.11', '-Oqv', 'ENTITY-MIB');
