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

$fan_oper_status = '.1.3.6.1.4.1.2272.1.4.7.1.1.2';
$fan = snmpwalk_cache_oid_num($device, $fan_oper_status, array());

if (is_array($fan)) {
    foreach ($fan as $oid => $array) {
        $state = current($array);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $current_oid = "$fan_oper_status.$index";
        $descr =  "Fan $index";

        $state_name = 'rcChasFanOperStatus';
        $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'),
            array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'up'),
            array('value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'down'),
        );
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $current_oid, "rcChasFanOperStatus.$index", $state_name, $descr, '1', '1', null, null, '3', '3', $state);
        create_sensor_to_state_index($device, $state_name, "rcChasFanOperStatus.$index");
    }
}

/*  rcChasPowerSupplyOperStatus
 *  unknown(1),
 *  empty(2),
 *  up(3),
 *  down(4)
*/

$power_supply_oper_status = '.1.3.6.1.4.1.2272.1.4.8.1.1.2';
$power_supply = snmpwalk_cache_oid_num($device, $power_supply_oper_status, array());

if (is_array($power_supply)) {
    foreach ($power_supply as $oid => $array) {
        $state = current($array);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        $current_oid = "$power_supply_oper_status.$index";
        $descr =  "Power Supply $index";

        $state_name = 'rcChasPowerSupplyOperStatus';
        $states = array(
            array('value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'),
            array('value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'empty'),
            array('value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'up'),
            array('value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'down'),
        );
        create_state_index($state_name, $states);

        discover_sensor($valid['sensor'], 'state', $device, $current_oid, "rcChasPowerSupplyOperStatus.$index", $state_name, $descr, '1', '1', null, null, '4', '4', $state);
        create_sensor_to_state_index($device, $state_name, "rcChasPowerSupplyOperStatus.$index");
    }
}
