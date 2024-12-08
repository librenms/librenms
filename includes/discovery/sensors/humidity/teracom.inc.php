<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2024 Config Services <team@configuration.co.uk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @author     Config Services <team@configuration.co.uk>
*/

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

$teracom_devices = [
    // Only supported by certain models
    'TCW220' => [
        's11Int' => '.1.3.6.1.4.1.38783.2.3.1.1.1.0',
        's21Int' => '.1.3.6.1.4.1.38783.2.3.1.2.1.0',
        's31Int' => '.1.3.6.1.4.1.38783.2.3.1.3.1.0',
        's41Int' => '.1.3.6.1.4.1.38783.2.3.1.4.1.0',
        's51Int' => '.1.3.6.1.4.1.38783.2.3.1.5.1.0',
        's61Int' => '.1.3.6.1.4.1.38783.2.3.1.6.1.0',
        's71Int' => '.1.3.6.1.4.1.38783.2.3.1.7.1.0',
        's81Int' => '.1.3.6.1.4.1.38783.2.3.1.8.1.0',
    ],
    'TCW241' => [
        's11Int' => '.1.3.6.1.4.1.38783.3.3.1.1.1.0',
        's21Int' => '.1.3.6.1.4.1.38783.3.3.1.2.1.0',
        's31Int' => '.1.3.6.1.4.1.38783.3.3.1.3.1.0',
        's41Int' => '.1.3.6.1.4.1.38783.3.3.1.4.1.0',
        's51Int' => '.1.3.6.1.4.1.38783.3.3.1.5.1.0',
        's61Int' => '.1.3.6.1.4.1.38783.3.3.1.6.1.0',
        's71Int' => '.1.3.6.1.4.1.38783.3.3.1.7.1.0',
        's81Int' => '.1.3.6.1.4.1.38783.3.3.1.8.1.0',
    ],
];

if (Arr::exists($teracom_devices, $device['hardware'])) {
    $teracom_mib = 'TERACOM-' . strtoupper($device['hardware']) . '-MIB';
    $teracom_sensorsSetup = SnmpQuery::cache()->hideMib()->walk("$teracom_mib::sensorsSetup")->table(1);
    $teracom_sensors = SnmpQuery::cache()->hideMib()->walk("$teracom_mib::sensors")->table(1);
    $teracom_temps = array_merge($teracom_sensors[0], $teracom_sensorsSetup[0]);
    foreach ($teracom_temps as $t_k => $t_v) {
        // Reformat the array to make it easier to use.
        preg_match("/(s[\d])([\d]*)(.*)/", $t_k, $t_d);
        if ($t_d[2] == '') {
            $teracom_data[$t_d[1]][$t_d[3]] = $t_v;
        } else {
            $teracom_data[$t_d[1]][$t_d[2]][$t_d[3]] = $t_v;
        }
    }

    foreach ($teracom_data as $t_sensor => $t_data) {
        if (Str::contains($t_data['description'], [':TSH2'])) {
            // This is the only sensor that can be attached. Description doesn't contain them if no sensor attached.
            $divisor = 1000;
            $oid = $teracom_devices[$device['hardware']][$t_sensor . '2Int'];
            $index = $device['hardware'] . $t_sensor . '2Int';
            $high_limit = $t_data[1]['MAXInt'];
            $low_limit = $t_data[1]['MINInt'];
            $current = $t_data[1]['Int'];

            discover_sensor(null, 'humidity', $device, $oid, $index, 'teracom', $t_data['description'], $divisor, '1', $low_limit, null, null, $high_limit, $current);
        }
    }
}

unset(
    $teracom_mib,
    $teracom_temp_value,
    $teracom_sensorsSetup,
    $teracom_sensors,
    $teracom_temps,
    $teracom_data,
);
