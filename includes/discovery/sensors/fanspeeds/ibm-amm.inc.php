<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'ibm-amm') {

    $oids = array('blower1speedRPM', 'blower1speedRPM', 'blower1speedRPM', 'blower1speedRPM');
    d_echo($oids."\n");
    if (!empty($oids)) {

        echo 'BLADE-MIB';
        foreach ($oids as $data) {

            if (!empty($data)) {
                $value = snmp_get($device, $oid.'.0', '-OsqnU', 'BLADE-MIB');
                if (is_numeric($value)) {
                    $descr = $data;
                    discover_sensor($valid['sensor'], 'fanspeed', $device, $data, 0, 'snmp', $descr, 1, 1, null, null, null, null, $value);
                }

            }

        }

    }

}
