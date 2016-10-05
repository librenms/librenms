<?php
/*
 * LibreNMS module to create sensors from Cisco Integrated Management Controllers (CIMC)
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'cimc') {
    // Let's add some temperature sensors.
    $temp_board = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.719.1.9.44.1');
    $temp_mem = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.719.1.30.12.1');
    $temp_cpu = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.719.1.41');

    /*
     * False == OID not found - this is not an error.
     * null  == timeout or something else that caused an error.
     */
    if (is_null($temp_board) || is_null($temp_mem) || is_null($temp_cpu)) {
        echo "Error\n";
    } else {
        // No Error, lets process things.

        // Board Temperatures
        foreach ($temp_board['1.3.6.1.4.1.9.9.719.1.9.44.1.2'] as $index => $string) {
            $temp = preg_match('/sys\/(rack-unit-[^,]+)\/board\/temp-stats/', $string, $regexp_result);
            $description = $regexp_result[1];

            // Ambient Temperature
            $oid = '.1.3.6.1.4.1.9.9.719.1.9.44.1.4'.$index;
            d_echo($oid." - ".$description." - Ambient: ".$temp_board[$oid][$index]."\n");
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'ambient-'.$index, 'cimc', $description." - Ambient", '1', '1', null, null, null, null, $temp_board[$oid][$index]);

            // Front Temperature
            $oid = '.1.3.6.1.4.1.9.9.719.1.9.44.1.8'.$index;
            d_echo($oid." - ".$description." - Front: ".$temp_board[$oid][$index]."\n");
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'front-'.$index, 'cimc', $description." - Front", '1', '1', null, null, null, null, $temp_board[$oid][$index]);

            // Rear Temperature
            $oid = '.1.3.6.1.4.1.9.9.719.1.9.44.1.21.'.$index;
            d_echo($oid." - ".$description." - Rear: ".$temp_board[$oid][$index]."\n");
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'rear-'.$index, 'cimc', $description." - Rear", '1', '1', null, null, null, null, $temp_board[$oid][$index]);

            // IO Hub Temperature
            $oid = '.1.3.6.1.4.1.9.9.719.1.9.44.1.13.'.$index;
            d_echo($oid." - ".$description." - IO Hub: ".$temp_board[$oid][$index]."\n");
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'ioh-'.$index, 'cimc', $description." - IO Hub", '1', '1', null, null, null, null, $temp_board[$oid][$index]);
        }

        // Memory Temperatures
        foreach ($temp_mem['1.3.6.1.4.1.9.9.719.1.30.12.1.2'] as $index => $string) {
            $temp = preg_match('/sys\/(rack-unit-[^,]+)\/memarray-1\/(mem-[^,]+)\/dimm-env-stats/', $string, $regexp_result);
            $description = $regexp_result[1]." - ".$regexp_result[2];

            // DIMM Temperature
            $oid = '.1.3.6.1.4.1.9.9.719.1.30.12.1.6.'.$index;
            d_echo($oid." - ".$description." - ".$temp_mem[$oid][$index]."\n");
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'mem-'.$index, 'cimc', $description, '1', '1', null, null, 40, null, $temp_mem[$oid][$index]);
        }

        // CPU Temperatures
        foreach ($temp_cpu['1.3.6.1.4.1.9.9.719.1.41.2.1.2'] as $index => $string) {
            $temp = preg_match('/sys\/(rack-unit-[^,]+)\/board\/(cpu-[^,]+)\/env-stats/', $string, $regexp_result);
            $description = $regexp_result[1]." - ".$regexp_result[2];

            // CPU Temperature
            $oid = '.1.3.6.1.4.1.9.9.719.1.41.2.1.10.'.$index;
            d_echo($oid." - ".$description." - ".$temp_cpu[$oid][$index]."\n");
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'cpu-'.$index, 'cimc', $description, '1', '1', null, null, 40, null, $temp_cpu[$oid][$index]);
        }
    }
}
