<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <aldemir.akpinar@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// switchModel:
$hardware = snmp_get($device, $device['sysObjectID'].'.1.2.0', '-OQvs');
// firmwareVersion:
$version = snmp_get($device, $device['sysObjectID'].'.1.4.0', '-OQvs');
// serialNumber (not supported on all models):
$serial = snmp_get($device, $device['sysObjectID'].'.1.78.0', '-OQvs');
