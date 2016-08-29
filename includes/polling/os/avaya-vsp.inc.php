<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * Copyright (c) 2016 Daniel Cox <danielcoxman@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// rcSysVersion
$version = snmp_get($device, '.1.3.6.1.4.1.2272.1.1.7.0', '-Oqvn');
$version = explode(' ', $version);
$version = $version[0];
$version = str_replace('"', '', $version);

// rcChasSerialNumber
$serial = snmp_get($device, '.1.3.6.1.4.1.2272.1.4.2.0', '-Oqvn');
$serial = str_replace('"', '', $serial);

// rcChasHardwareRevision
$sysDescr = $poll_device['sysDescr'];
$sysDescr = explode(' ', $sysDescr);
$sysDescr = $sysDescr[0];
$hwrevision = snmp_get($device, '.1.3.6.1.4.1.2272.1.4.3.0', '-Oqvn');
$hardware = $sysDescr . " HW: $hwrevision";
$hardware = str_replace('"', '', $hardware);

