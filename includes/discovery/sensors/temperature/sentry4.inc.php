<?php
/*
 * Copyright (c) 2016 Dropbox, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at

 *   http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
*/

$oids = snmp_walk($device, 'st4TempSensorValue', '-Osqn', 'Sentry4-MIB');
d_echo($oids."\n");

$oids       = trim($oids);
$divisor    = '10';
$multiplier = '1';
if ($oids) {
    echo 'ServerTech Sentry4 Temperature ';
    $sentry_temp_scale = snmp_get($device, 'st4TempSensorScale.0', '-Ovq', 'Sentry4-MIB');

    echo 'ServerTech Sentry4 Temperature ';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid, $descr) = explode(' ', $data, 2);
            $split_oid = explode('.', $oid);
            $index = substr($oid, -3);

            // Sentry4-MIB::st4TempSensorValue
            $temperature_oid = '.1.3.6.1.4.1.1718.4.1.9.3.1.1.'.$index;
            $descr = snmp_get($device, "st4TempSensorName.$index", '-Ovq', 'Sentry4-MIB');
            $low_warn_limit = snmp_get($device, "st4TempSensorLowWarning.$index", '-OQUnv', 'Sentry4-MIB');
            $low_limit = snmp_get($device, "st4TempSensorLowAlarm.$index", '-OQUnv', 'Sentry4-MIB');
            $high_warn_limit = snmp_get($device, "st4TempSensorHighWarning.$index", '-OQUnv', 'Sentry4-MIB');
            $high_limit = snmp_get($device, "st4TempSensorHighAlarm.$index", '-OQUnv', 'Sentry4-MIB');
            $current = (snmp_get($device, "$temperature_oid", '-OvqU', 'Sentry4-MIB') / $divisor);

            if ($sentry_temp_scale == 'fahrenheit') {
                $low_warn_limit = fahrenheit_to_celsius($low_warn_limit, $sentry_temp_scale);
                $low_limit = fahrenheit_to_celsius($low_limit, $sentry_temp_scale);
                $high_warn_limit = fahrenheit_to_celsius($high_warn_limit, $sentry_temp_scale);
                $high_limit = fahrenheit_to_celsius($high_limit, $sentry_temp_scale);
                $current = fahrenheit_to_celsius($current, $sentry_temp_scale);
            }

            if (is_numeric($current) && $current >= 0) {
                discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_oid, 'sentry4', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current, 'snmp', null, null, null);
            }
        }
    }
}
