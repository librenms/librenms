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


if ($device['os'] == "nos") {
    $oids = snmp_walk($device, '.1.3.6.1.4.1.1588.2.1.1.1.1.22.1.2', '-Osqn');
    $oids = trim($oids);
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data and $data[37] == "2") {
            $value_oid = ".1.3.6.1.4.1.1588.2.1.1.1.1.22.1.4.".$data[35];
            $descr_oid = ".1.3.6.1.4.1.1588.2.1.1.1.1.22.1.5.".$data[35];
            $value = snmp_get($device, $value_oid, '-Oqv');
            $descr = snmp_get($device, $descr_oid, '-Oqv');
            if (!strstr($descr, 'No') and !strstr($value, 'No')) {
                $descr = str_replace('"', '', $descr);
                $descr = trim($descr);
                discover_sensor($valid['sensor'], 'temperature', $device, $value_oid, $data[35], 'nos', $descr, '1', '1', null, null, '80', '100', $value);
            }
        }
    }
}
