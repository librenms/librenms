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
 *
 *  rcVossSystemFanInfoOperStatus or rcChasFanOperStatus
 *  unknown(1),
 *  up(2),
 *  down(3),
 *  notpresent(4)
 */
$voss_fan = snmpwalk_cache_multi_oid($device, 'rcVossSystemFanInfoOperStatus', [], 'RAPID-CITY');
$fan = snmpwalk_cache_multi_oid($device, 'rcChasFanOperStatus', [], 'RAPID-CITY');

if (is_array($voss_fan)) {
    foreach ($voss_fan as $oid => $array) {
        $state = current($array);
        $split_oid = explode('.', $oid);
        $tray_num = $split_oid[(count($split_oid) - 2)];
        $fan_num = $split_oid[(count($split_oid) - 1)];
        $current_oid = ".1.3.6.1.4.1.2272.1.101.1.1.4.1.4.$tray_num.$fan_num";
        $descr = "VOSS Tray $tray_num Fan $fan_num";

        $state_name = 'rcVossSystemFanInfoOperStatus';
        $states = [
            ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'down'],
            ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
        ];
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $current_oid, "rcVossSystemFanInfoOperStatus.$tray_num.$fan_num", $state_name, $descr, 1, 1, null, null, 3, 3, $state);
        create_sensor_to_state_index($device, $state_name, "rcVossSystemFanInfoOperStatus.$tray_num.$fan_num");
    }
} elseif (is_array($fan)) {
    foreach ($fan as $oid => $array) {
        $state = current($array);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $current_oid = ".1.3.6.1.4.1.2272.1.4.7.1.1.2.$index";
        $descr = "VOSS Fan $index";

        $state_name = 'rcChasFanOperStatus';
        $states = [
            ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
            ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'down'],
            ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
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
        $descr = "VOSS Power Supply $index";

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
