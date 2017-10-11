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

$descr = 'Power Usage';
// Moxa people enjoy creating MIBs for each model!
if ($device['sysDescr'] == 'IKS-6726A-2GTXSFP-T') {
    $value = snmp_get($device, 'powerConsumption.0', '-Ovq', 'MOXA-IKS6726A-MIB');
} else if ($device['sysDescr'] == 'EDS-G508E-T') {
    $value = snmp_get($device, 'powerConsumption.0', '-Ovq', 'MOXA-EDSG508E-MIB');
}

if (is_numeric($value)) {
    discover_sensor($valid['sensor'], 'power', $device, "powerConsumption.0", '0', 'moxa-etherdevice', $descr, '1', '1', null, null, null, null, $value);
}
