<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:CISCO-PRODUCTS-MIB');

$version = snmp_get($device, '.1.3.6.1.2.1.54.1.1.1.1.4.1', '-Osqv');

$applistraw = snmpwalk_cache_oid($device, ".1.3.6.1.2.1.54.1.1.1.1.3", array());

foreach($applistraw as $oid => $app) {
     foreach($app as $oid2 => $app2) {
     if($app2 == "Cisco Unified CCX Database") {
     $features = "UCCX";
     } elseif($app2 == "Cisco CallManager") {
     $features = "CUCM";
     } elseif($app2 == "Cisco Emergency Responder") {
     $features = "CER";
     } elseif($app2 == "Connection System Agent") {
     $features = "CUC";
     }
}
}
