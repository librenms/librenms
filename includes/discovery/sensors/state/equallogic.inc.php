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


$oids_disks = snmp_walk($device, 'eqlDiskSerialNumber', '-OQn', 'EQLDISK-MIB', 'equallogic');

d_echo('Disk Serials oids:\n');
d_echo($oids_disks."\n");

/*
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.1 = RZ1wTmZ00980
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.2 = RZ1wTmZ00964
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.3 = RZ1wTmZ0095E
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.4 = RZ1wTmZ0096W
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.5 = RZ1wTmZ0097U
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.6 = RZ1wTmZ008RK
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.7 = RZ1wTmZ0098D
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.8 = RZ1wTmZ0095C
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.9 = RZ1wTmZ00970
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.10 = RZ1wTmZ0094
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.11 = RZ1wTmZ85BZ
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.12 = RZ1wTmZ00958
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.13 = RZ1wTmZ00983
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.14 = RZ1wTmZ0096D
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.15 = RZ1wTmZ00952
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.16 = RZ1wTmZ00962
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.17 = RZ1wTmZ0095N
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.18 = RZ1wTmZ0097M
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.19 = 6RZ1wTmZ3QS5
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.20 = RZ1wTmZ0091H
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.21 = RZ1wTmZ00981
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.22 = RZ1wTmZ0097H
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.23 = RZ1wTmZ00959
    .1.3.6.1.4.1.12740.3.1.1.1.5.1.329840783.24 = RZ1wTmZ0098B
**/

$base_oid         = '.1.3.6.1.4.1.12740.3.1.1.1.8.1.'; // eqlDiskStatus

if (!empty($oids_disks)) {
/*
eqlDiskStatus
    INTEGER {
            on-line  (1),
            spare    (2),
            failed   (3),
            off-line (4),
            alt-sig  (5),
            too-small(6),
            history-of-failures(7),
            unsupported-version(8)
    }
*/
    $state_name1 = 'eqlDiskStatus';
    $state_index_id1 = create_state_index($state_name1);

    if ($state_index_id1) {
        $states1 = array(
            array($state_index_id1,'on-line',1,1,0),
            array($state_index_id1,'spare',1,2,0),
            array($state_index_id1,'failed',1,3,2),
            array($state_index_id1,'off-line',1,4,1),
            array($state_index_id1,'alt-sig',1,5,1),
            array($state_index_id1,'too-small',1,6,2),
            array($state_index_id1,'history-of-failures',1,7,1),
            array($state_index_id1,'unsupported-version',1,8,1),
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

    foreach (explode("\n", $oids_disks) as $data) {
        $data = trim($data);
        if (!empty($data)) {
            list($oid,$descr) = explode(' = ', $data, 2);
            $split_oid        = explode('.', $oid);
            $disk_index        = $split_oid[(count($split_oid) - 1)];
            $index            = (int)$disk_index+0;
            $member_id        = $split_oid[(count($split_oid) - 2)];
            $num_index        = $member_id.'.'.$disk_index;
            $oid              = $base_oid.$num_index;
            $extra            = snmp_get_multi($device, $oid, '-OQne', 'EQLDISK-MIB', 'equallogic');
            d_echo($extra);
            if (!empty($extra)) {
                list($foid,$pstatus) = explode(' = ', $extra, 2);
                $index        = (100 + $index);
                $low_limit    = 0.5;
                $high_limit   = 1.5;
                discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name1, "Disk $disk_index - $descr", 1, 1, $low_limit, $low_limit, $high_limit, $high_limit, $pstatus, 'snmp', $index);
                create_sensor_to_state_index($device, $state_name1, $index);
            }
        }//end if
    }//end foreach
}//end if empty oids
