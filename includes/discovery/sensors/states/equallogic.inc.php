<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Peter TKATCHENKO https://github.com/Peter2121/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = snmp_walk($device, 'eqlMemberHealthStatus', '-OQne', 'EQLMEMBER-MIB', 'equallogic');

d_echo('Health oids:');
d_echo($oids."\n");

/*
eqlMemberHealthStatus
    INTEGER     {
                unknown  (0),
                normal   (1),
                warning  (2),
                critical (3)
                }
    DESCRIPTION     The value of this object is determinted by the severity of the
                    health condition state variables. The most severe state will
                    be reflected.

The LibreNMS generic states is derived from Nagios:
    0 = OK
    1 = Warning
    2 = Critical
    3 = Unknown

*/

if (!empty($oids)) {
    $descr='Health';

    $state_name = 'eqlMemberHealthStatus';
    $state_index_id = create_state_index($state_name);

    if ($state_index_id) {
        $states = array(
            array($state_index_id,'unknown',0,0,3) ,
            array($state_index_id,'normal',1,1,0) ,
            array($state_index_id,'warning',1,2,1) ,
            array($state_index_id,'critical',1,3,2)
        );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],
                'state_generic_value' => $value[4]
            );
            dbInsert($insert, 'state_translations');
        }
    }

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if (!empty($data)) {
            list($oid,$current) = explode(' = ', $data, 2);
            $split_oid        = explode('.', $oid);
            $num_index        = $split_oid[(count($split_oid) - 1)];
            $index            = (int)$num_index+0;
            $low_limit        = 0.5;
            $high_limit       = 2.5;
            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, $low_limit, $low_limit, $high_limit, $high_limit, $current, 'snmp', $index);
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}

$oids1 = snmp_walk($device, 'eqlMemberHealthDetailsPowerSupplyName', '-OQn', 'EQLMEMBER-MIB', 'equallogic');

d_echo('PowerSupplyName oids:');
d_echo($oids1."\n");

/*
    .1.3.6.1.4.1.12740.2.1.8.1.2.1.329840783.1 = Power Cooling Module 0
    .1.3.6.1.4.1.12740.2.1.8.1.2.1.329840783.2 = Power Cooling Module 1
**/

$base_oid         = '.1.3.6.1.4.1.12740.2.1.8.1.3.1.'; // eqlMemberHealthDetailsPowerSupplyCurrentState

if (!empty($oids1)) {
/*
eqlMemberHealthDetailsPowerSupplyCurrentState
    INTEGER {
            on-and-operating    (1),
            no-ac-power         (2),
            failed-or-no-data   (3) -- has ac but no dc out or we have no data
    }
*/
    $state_name1 = 'eqlMemberPowerSupplyCurrentState';
    $state_index_id1 = create_state_index($state_name1);

    if ($state_index_id1) {
        $states1 = array(
            array($state_index_id1,'on-and-operating',1,1,0) ,
            array($state_index_id1,'no-ac-power',1,2,1) ,
            array($state_index_id1,'failed-or-no-data',1,3,2)
        );
        foreach ($states1 as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],
                'state_generic_value' => $value[4]
            );
            dbInsert($insert, 'state_translations');
        }
    }

    foreach (explode("\n", $oids1) as $data) {
        $data = trim($data);
        if (!empty($data)) {
            list($oid,$descr) = explode(' = ', $data, 2);
            $split_oid        = explode('.', $oid);
            $num_index        = $split_oid[(count($split_oid) - 1)];
            $index            = (int)$num_index+0;
            $member_id        = $split_oid[(count($split_oid) - 2)];
            $num_index        = $member_id.'.'.$num_index;
            $oid              = $base_oid.$num_index;
            $extra            = snmp_get_multi($device, $oid, '-OQne', 'EQLMEMBER-MIB', 'equallogic');
            d_echo($extra);
            if (!empty($extra)) {
                list($foid,$pstatus) = explode(' = ', $extra, 2);
                $index        = (100 + $index);
                $low_limit    = 0.5;
                $high_limit   = 1.5;
                discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name1, $descr, 1, 1, $low_limit, $low_limit, $high_limit, $high_limit, $pstatus, 'snmp', $index);
                create_sensor_to_state_index($device, $state_name1, $index);
            }
        }//end if
    }//end foreach
}//end if empty oids
