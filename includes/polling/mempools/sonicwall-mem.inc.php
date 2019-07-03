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

echo 'SonicWALL-MEMORY-POOL: ';
if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.8741.6')) {
    $usage = snmp_get($device, 'SNWL-SSLVPN-MIB::memoryUtilization.0', '-Ovq');
} else {
    $usage = snmp_get($device, 'SONICWALL-FIREWALL-IP-STATISTICS-MIB::sonicCurrentRAMUtil.0', '-Ovq');
}

$perc = str_replace('"', "", $usage);


if (is_numeric($perc)) {
    $mempool['perc'] = $perc;
    $mempool['used'] = $perc;
    $mempool['total'] = 100;
    $mempool['free'] = 100 - $perc;
}
