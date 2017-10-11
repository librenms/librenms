<?php
/*
 * LibreNMS Moxa EtherDevice Memory information module
 *
 * Copyright (c) 2017 Aldemir Akpinar <aldemir.akpinar@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
/*
-- CPU Loading and Free Memory info.
       totalMemory OBJECT-TYPE
        freeMemory OBJECT-TYPE
            "Total size of free dynamic memory"
     usedMemory OBJECT-TYPE
            "Total size of used dynamic memory"
     memoryUsage OBJECT-TYPE
            "The usage of memory size in %."
*/

// Moxa people enjoy creating similar MIBs for each model!
if ($device['sysDescr'] == 'IKS-6726A-2GTXSFP-T') {
    $mibmod = 'MOXA-IKS6726A-MIB';
} else if ($device['sysDescr'] == 'EDS-G508E-T') {
    $mibmod = 'MOXA-EDSG508E-MIB';
}

$mempool['total'] = snmp_get($device, "totalMemory.0", '-OvQ', $mibmod);
$mempool['used'] = snmp_get($device, "usedMemory.0", '-OvQ', $mibmod);
$mempool['free'] = snmp_get($device, "freeMemory.0", '-OvQ', $mibmod);
