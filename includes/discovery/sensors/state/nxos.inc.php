<?php
/*
 * LibreNMS NX-OS Fan state
 *
 * Copyright (c) 2016 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$fan_tray_oid = '.1.3.6.1.4.1.9.9.117.1.4.1.1.1';
$fan_trays = snmpwalk_cache_oid($device, $fan_tray_oid, []);

/* CISCO-ENTITY-FRU-CONTROL-MIB cefcFanTrayOperStatus
 *  unknown(1),
 *  up(2),
 *  down(3),
 *  warning(4)
*/

if (is_array($fan_trays)) {
    foreach ($fan_trays as $oid => $array) {
        $state = current($array);
        $split_oid = explode('.', $oid);
        $index = $split_oid[count($split_oid) - 1];
        $current_oid = "$fan_tray_oid.$index";

        $entity_oid = '.1.3.6.1.2.1.47.1.1.1.1.7';
        $descr = trim(snmp_get($device, "$entity_oid.$index", '-Ovq'), '"');

        $state_name = 'cefcFanTrayOperStatus';
        $states = [
            ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'up'],
            ['value' => 3, 'generic' => 2, 'graph' => 1, 'descr' => 'down'],
            ['value' => 4, 'generic' => 1, 'graph' => 1, 'descr' => 'warning'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $current_oid, $index, $state_name, $descr, 1, 1);
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

$error_disabled_oid = '.1.3.6.1.4.1.9.9.548.1.3.1.1.2';
$interface_oid = '.1.3.6.1.2.1.31.1.1.1.1';
$interface_results = snmpwalk_cache_oid($device, $interface_oid, []);
if (is_array($interface_results)) {
    foreach ($interface_results as $oid => $array) {
        $split_oid = explode('.', $oid);
        $index = $split_oid[count($split_oid) - 1];
        $state = trim(snmp_get($device, "$error_disabled_oid.$index.0", '-Ovq'), '"');
        if ($state == 'No Such Instance currently exists at this OID') {
            $state = 0;
        }

        $descr = 'Suspended Status for ' . current($array);
        $current_oid = "$error_disabled_oid.$index.0";

        $state_name = 'cErrDisableIfStatusCause';
        $states = [
            ['value' => 0, 'graph' => 1, 'generic' => 0, 'descr' => 'OK'],
            ['value' => 1, 'graph' => 1, 'generic' => 2, 'descr' => 'udld'],
            ['value' => 2, 'graph' => 1, 'generic' => 2, 'descr' => 'bpduGuard'],
            ['value' => 3, 'graph' => 1, 'generic' => 2, 'descr' => 'channelMisconfig'],
            ['value' => 4, 'graph' => 1, 'generic' => 2, 'descr' => 'pagpFlap'],
            ['value' => 5, 'graph' => 1, 'generic' => 2, 'descr' => 'dtpFlap'],
            ['value' => 6, 'graph' => 1, 'generic' => 2, 'descr' => 'linkFlap'],
            ['value' => 7, 'graph' => 1, 'generic' => 2, 'descr' => 'l2ptGuard'],
            ['value' => 8, 'graph' => 1, 'generic' => 2, 'descr' => 'dot1xSecurityViolation'],
            ['value' => 9, 'graph' => 1, 'generic' => 2, 'descr' => 'portSecurityViolation'],
            ['value' => 10, 'graph' => 1, 'generic' => 2, 'descr' => 'gbicInvalid'],
            ['value' => 11, 'graph' => 1, 'generic' => 2, 'descr' => 'dhcpRateLimit'],
            ['value' => 12, 'graph' => 1, 'generic' => 2, 'descr' => 'unicastFlood'],
            ['value' => 13, 'graph' => 1, 'generic' => 2, 'descr' => 'vmps'],
            ['value' => 14, 'graph' => 1, 'generic' => 2, 'descr' => 'stormControl'],
            ['value' => 15, 'graph' => 1, 'generic' => 2, 'descr' => 'inlinePower'],
            ['value' => 16, 'graph' => 1, 'generic' => 2, 'descr' => 'arpInspection'],
            ['value' => 17, 'graph' => 1, 'generic' => 2, 'descr' => 'portLoopback'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $current_oid, $index, $state_name, $descr, 1, 1);
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
