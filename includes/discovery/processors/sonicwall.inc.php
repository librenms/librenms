<?php
/*
 * LibreNMS SonicWALL CPU information module
 *
 * Copyright (c) 2015 Mark Nagel <mnagel@willingminds.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'sonicwall') {
    echo 'SonicWALL CPU: ';

    $usage_oid = 'SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicCurrentCPUUtil.0';
    $usage = snmp_get($device, $usage_oid, '-Ovqn');
    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, $usage_oid, '0', 'sonicwall', 'CPU', '1', $usage, null, null);
    }
}

unset($processors_array);
