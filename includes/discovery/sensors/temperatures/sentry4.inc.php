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
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid,$descr) = explode(' ', $data, 2);
        $split_oid        = explode('.', $oid);
        $index            = $split_oid[(count($split_oid) - 1)];

        // Sentry4-MIB::st4TempSensorValue
        $temperature_oid = '.1.3.6.1.4.1.1718.4.1.9.3.1.1.1.'.$index;
        $descr           = 'Removable Sensor '.$index;
        $low_warn_limit  = (snmp_get($device, "st4TempSensorLowWarning.1.$index", '-Ovq', 'Sentry4-MIB') / $divisor);
        $low_limit       = (snmp_get($device, "st4TempSensorLowAlarm.1.$index", '-Ovq', 'Sentry4-MIB') / $divisor);
        $high_warn_limit = (snmp_get($device, "st4TempSensorHighWarning.1.$index", '-Ovq', 'Sentry4-MIB') / $divisor);
        $high_limit      = (snmp_get($device, "st4TempSensorHighAlarm.1.$index", '-Ovq', 'Sentry4-MIB') / $divisor);
        $current         = (snmp_get($device, "$temperature_oid", '-Ovq', 'Sentry4-MIB') / $divisor);

        if ($current >= 0) {
            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $index, 'sentry4', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
        }
    }
}
