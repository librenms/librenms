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

$temp = snmpwalk_cache_multi_oid($device, 'temperatureProbeTable', [], 'MIB-Dell-10892', 'dell');
$cur_oid = '.1.3.6.1.4.1.674.10892.1.700.20.1.6.';
$divisor = '10';

if (is_array($temp)) {
    foreach ($temp as $index => $entry) {
        $descr = $temp[$index]['temperatureProbeLocationName'];
        (isset($temp[$index]['temperatureProbeReading'])) ? $value = $temp[$index]['temperatureProbeReading'] / $divisor : $value = null;
        (isset($temp[$index]['temperatureProbeLowerCriticalThreshold'])) ? $lowlimit = $temp[$index]['temperatureProbeLowerCriticalThreshold'] / $divisor : $lowlimit = null;
        (isset($temp[$index]['temperatureProbeLowerNonCriticalThreshold'])) ? $low_warn_limit = $temp[$index]['temperatureProbeLowerNonCriticalThreshold'] / $divisor : $low_warn_limit = null;
        (isset($temp[$index]['temperatureProbeUpperNonCriticalThreshold'])) ? $warnlimit = $temp[$index]['temperatureProbeUpperNonCriticalThreshold'] / $divisor : $warnlimit = null;
        (isset($temp[$index]['temperatureProbeUpperCriticalThreshold'])) ? $limit = $temp[$index]['temperatureProbeUpperCriticalThreshold'] / $divisor : $limit = null;

        discover_sensor(null, 'temperature', $device, $cur_oid . $index, $index, 'dell', $descr, $divisor, '1', $lowlimit, $low_warn_limit, $warnlimit, $limit, $value, 'snmp', $index);

        unset(
            $descr,
            $value,
            $lowlimit,
            $low_warn_limit,
            $warnlimit,
            $limit
        );
    }
}
