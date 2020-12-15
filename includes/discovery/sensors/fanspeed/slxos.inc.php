<?php
/*
 * LibreNMS module for Brocade NOS fanspeed sensor
 *
 * Copyright (c) 2016 Maxence POULAIN <maxence.poulain@bsonetwork.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = snmp_walk($device, '.1.3.6.1.4.1.1588.2.1.1.1.1.22.1.2', '-Osqn');
$oids = trim($oids);
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    [$dataoid,$dataval] = explode(' ', $data);
    $oidparts = explode('.', $dataoid);
    $oididx = $oidparts[count($oidparts) - 1];
    if ($data and $dataval == '2') {
        $value_oid = '.1.3.6.1.4.1.1588.2.1.1.1.1.22.1.4.' . $oididx;
        $descr_oid = '.1.3.6.1.4.1.1588.2.1.1.1.1.22.1.5.' . $oididx;
        $value = snmp_get($device, $value_oid, '-Oqv');
        $descr = snmp_get($device, $descr_oid, '-Oqv');
        if (! strstr($descr, 'No') and ! strstr($value, 'No')) {
            $descr = str_replace('"', '', $descr);
            $descr = trim($descr);
            discover_sensor($valid['sensor'], 'fanspeed', $device, $value_oid, $oididx, 'nos', $descr, '1', '1', null, null, '80', '100', $value);
        }
    }
}
