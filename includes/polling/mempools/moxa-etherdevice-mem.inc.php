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
} elseif ($device['sysDescr'] == 'EDS-G508E-T') {
    $mibmod = 'MOXA-EDSG508E-MIB';
} elseif ($device['sysDescr'] == 'EDS-P510A-8PoE-2GTXSFP-T') {
    $mibmod = 'MOXA-EDSP510A8POE-MIB';
} elseif ($device['sysDescr'] == 'EDS-G512E-8PoE-T') {
    $mibmod = 'MOXA-EDSG512E8POE-MIB';
}

$mem_res = snmp_get_multi_oid($device, ['totalMemory.0', 'usedMemory.0', 'freeMemory.0'], '-OQUs', $mibmod);

d_echo(serialize($mem_res));

$mempool['total'] = $mem_res['totalMemory.0'];
$mempool['used'] = $mem_res['usedMemory.0'];
$mempool['free'] = $mem_res['freeMemory.0'];
