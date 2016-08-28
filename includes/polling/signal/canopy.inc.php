<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$cambium_type = snmp_get($device, 'sysDescr.0', '-Oqv', '');
if (strstr($cambium_type, 'BHUL450-DES') || stristr($cambium_type, 'BHUL450-AES')) {
    $masterSlaveMode = snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    if ($masterSlaveMode == "timingMaster") {
        $mib = ':WHISP-APS-MIB';
    } else {
        $mib = ':WHISP-SM-MIB';
    }
} elseif (strstr($cambium_type, 'BHUL') || stristr($cambium_type, 'BH')) {
    $masterSlaveMode = snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
    if ($masterSlaveMode == "timingMaster") {
        $mib = ':WHISP-APS-MIB';
    } else {
        $mib = ':WHISP-BOX-MIBV2-MIB';
    }
} else {
    $mib = ':WHISP-BOX-MIBV2-MIB';
}
