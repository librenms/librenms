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

echo 'Moxa EtherDevice CPU Usage';
// Moxa people enjoy creating MIBs for each model!
if ($device['sysDescr'] == 'IKS-6726A-2GTXSFP-T') {
    $mibmod = 'MOXA-IKS6726A-MIB';
} else if ($device['sysDescr'] == 'EDS-G508E-T') {
    $mibmod = 'MOXA-EDSG508E-MIB';
}

$usage = snmp_get($device, 'cpuLoading30s.0', '-Ovq', $mibmod);

d_echo($usage."\n");
if (is_numeric($usage)) {
    $proc = $usage;
}
