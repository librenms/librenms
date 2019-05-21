<?php
/**
 * voss.inc.php
 *
 * LibreNMS Fan and Power Supply state Discovery module for Extreme/Avaya
 * VOSS(VSP Operating System Software)
 *
 * Copyright (c) 2017 Daniel Cox <danielcoxman@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

/*  rcChasFanOperStatus
 *  unknown(1),
 *  up(2),
 *  down(3),
*/

$fan = snmpwalk_cache_multi_oid($device, 'rcChasFanOperStatus', [], 'RAPID-CITY');

if (is_array($fan)) {
    foreach ($fan as $oid => $array) {
        $state = current($array);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $current_oid = ".1.3.6.1.4.1.2272.1.4.7.1.1.2.$index";
        $descr =  "Fan $index";

        $state_name = 'rcChasFanOperStatus';
        $states = [
            ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'down'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $current_oid, "rcChasFanOperStatus.$index", $state_name, $descr, 1, 1, null, null, 3, 3, $state);
        create_sensor_to_state_index($device, $state_name, "rcChasFanOperStatus.$index");
    }
}

/*  rcChasPowerSupplyOperStatus
 *  unknown(1),
 *  empty(2),
 *  up(3),
 *  down(4)
*/

$power_supply = snmpwalk_cache_multi_oid($device, 'rcChasPowerSupplyOperStatus', [], 'RAPID-CITY');

if (is_array($power_supply)) {
    foreach ($power_supply as $oid => $array) {
        $state = current($array);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $current_oid = ".1.3.6.1.4.1.2272.1.4.8.1.1.2.$index";
        $descr =  "Power Supply $index";

        $state_name = 'rcChasPowerSupplyOperStatus';
        $states = [
            ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'empty'],
            ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
            ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'down'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $current_oid, "rcChasPowerSupplyOperStatus.$index", $state_name, $descr, 1, 1, null, null, 4, 4, $state);
        create_sensor_to_state_index($device, $state_name, "rcChasPowerSupplyOperStatus.$index");
    }
}
