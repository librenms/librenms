<?php
/*
 * LibreNMS Powervault state
 *
 * Copyright (c) 2017 Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$state = snmp_get($device, 'DELL-SHADOW-MIB::shadowStatusGlobalStatus.0', '-Oqne');
[$oid, $value] = explode(' ', $state);

if (is_numeric($value)) {
    $descr = 'Global Status';
    $state_name = 'shadowStatusGlobalStatus';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 2, 'descr' => 'other'],
        ['value' => 2, 'generic' => 0, 'graph' => 3, 'descr' => 'unknown'],
        ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 4, 'generic' => 0, 'graph' => 2, 'descr' => 'critical'],
        ['value' => 5, 'generic' => 0, 'graph' => 2, 'descr' => 'non-Recoverable'],
    ];
    create_state_index($state_name, $states);

    discover_sensor($valid['sensor'], 'state', $device, $oid, 1, $state_name, $descr, 1, 1);
    create_sensor_to_state_index($device, $state_name, 1);
}
