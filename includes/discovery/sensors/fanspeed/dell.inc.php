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

$temp = snmpwalk_cache_multi_oid($device, 'coolingDeviceTable', [], 'MIB-Dell-10892');
$cur_oid = '.1.3.6.1.4.1.674.10892.1.700.12.1.6.';

if (is_array($temp)) {
    foreach ($temp as $index => $entry) {
        $descr = $temp[$index]['coolingDeviceLocationName'];
        $value = $temp[$index]['coolingDeviceReading'];
        $lowlimit = $temp[$index]['coolingDeviceLowerCriticalThreshold'];
        $low_warn_limit = $temp[$index]['coolingDeviceLowerNonCriticalThreshold'];
        $warnlimit = $temp[$index]['coolingDeviceUpperNonCriticalThreshold'];
        $limit = $temp[$index]['coolingDeviceUpperCriticalThreshold'];

        discover_sensor($valid['sensor'], 'fanspeed', $device, $cur_oid . $index, $index, 'dell', $descr, '0', '1', $lowlimit, $low_warn_limit, $warnlimit, $limit, $value, 'snmp', $index);
    }
}
