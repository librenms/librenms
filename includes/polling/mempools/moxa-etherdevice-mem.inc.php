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

if ($device['os'] == 'moxa-etherdevice') {
    $perc     = snmp_get($device, "MOXA-IKS6726A-MIB::memoryUsage.0", '-OvQ');
    $mempool['total'] = snmp_get($device, "MOXA-IKS6726A-MIB::totalMemory.0", '-OvQ');
    $mempool['used'] = snmp_get($device, "MOXA-IKS6726A-MIB::usedMemory.0", '-OvQ');
    $mempool['free'] = snmp_get($device, "MOXA-IKS6726A-MIB::freeMemory.0", '-OvQ');
}
