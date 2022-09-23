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
d_echo($oids . "\n");

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

if (! empty($oids)) {
    $descr = 'Health';

    $state_name = 'eqlMemberHealthStatus';
    $states = [
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'normal'],
        ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'warning'],
        ['value' => 3, 'generic' => 2, 'graph' => 1, 'descr' => 'critical'],
    ];
    create_state_index($state_name, $states);

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if (! empty($data)) {
            [$oid,$current] = explode(' = ', $data, 2);
            $split_oid = explode('.', $oid);
            $num_index = $split_oid[(count($split_oid) - 1)];
            $index = (int) cast_number($num_index);
            $low_limit = 0.5;
            $high_limit = 2.5;
            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, $low_limit, $low_limit, $high_limit, $high_limit, $current, 'snmp', $index);
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}

$oids1 = snmp_walk($device, 'eqlMemberHealthDetailsPowerSupplyName', '-OQn', 'EQLMEMBER-MIB', 'equallogic');

d_echo('PowerSupplyName oids:');
d_echo($oids1 . "\n");

/*
    .1.3.6.1.4.1.12740.2.1.8.1.2.1.329840783.1 = Power Cooling Module 0
    .1.3.6.1.4.1.12740.2.1.8.1.2.1.329840783.2 = Power Cooling Module 1
**/

$base_oid = '.1.3.6.1.4.1.12740.2.1.8.1.3.1.'; // eqlMemberHealthDetailsPowerSupplyCurrentState

if (! empty($oids1)) {
    /*
    eqlMemberHealthDetailsPowerSupplyCurrentState
        INTEGER {
                on-and-operating    (1),
                no-ac-power         (2),
                failed-or-no-data   (3) -- has ac but no dc out or we have no data
        }
    */
    $state_name = 'eqlMemberPowerSupplyCurrentState';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'on-and-operating'],
        ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'no-ac-power'],
        ['value' => 3, 'generic' => 2, 'graph' => 1, 'descr' => 'failed-or-no-data'],
    ];
    create_state_index($state_name, $states);

    foreach (explode("\n", $oids1) as $data) {
        $data = trim($data);
        if (! empty($data)) {
            [$oid,$descr] = explode(' = ', $data, 2);
            $split_oid = explode('.', $oid);
            $num_index = $split_oid[(count($split_oid) - 1)];
            $index = (int) cast_number($num_index);
            $member_id = $split_oid[(count($split_oid) - 2)];
            $num_index = $member_id . '.' . $num_index;
            $oid = $base_oid . $num_index;
            $extra = snmp_get_multi($device, $oid, '-OQne', 'EQLMEMBER-MIB', 'equallogic');
            d_echo($extra);
            if (! empty($extra)) {
                [$foid,$pstatus] = explode(' = ', $extra, 2);
                $index = (100 + $index);
                $low_limit = 0.5;
                $high_limit = 1.5;
                discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, $low_limit, $low_limit, $high_limit, $high_limit, $pstatus, 'snmp', $index);
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }//end if
    }//end foreach
}//end if empty oids

$oids_disks = snmp_walk($device, 'eqlDiskSerialNumber', '-OQn', 'EQLDISK-MIB', 'equallogic');

d_echo('Disk Serials oids:' . PHP_EOL);
d_echo($oids_disks . "\n");

$disks_base_oid = '.1.3.6.1.4.1.12740.3.1.1.1.8.1.'; // eqlDiskStatus

if (! empty($oids_disks)) {
    $state_name = 'eqlDiskStatus';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'on-line'],
        ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'spare'],
        ['value' => 3, 'generic' => 2, 'graph' => 1, 'descr' => 'failed'],
        ['value' => 4, 'generic' => 1, 'graph' => 1, 'descr' => 'off-line'],
        ['value' => 5, 'generic' => 1, 'graph' => 1, 'descr' => 'alt-sig'],
        ['value' => 6, 'generic' => 2, 'graph' => 1, 'descr' => 'too-small'],
        ['value' => 7, 'generic' => 0, 'graph' => 1, 'descr' => 'history-of-failures'],
        ['value' => 8, 'generic' => 1, 'graph' => 1, 'descr' => 'unsupported-version'],
    ];
    create_state_index($state_name, $states);

    foreach (explode("\n", $oids_disks) as $data) {
        $data = trim($data);
        if (! empty($data)) {
            [$oid,$descr] = explode(' = ', $data, 2);
            $split_oid = explode('.', $oid);
            $disk_index = $split_oid[(count($split_oid) - 1)];
            $member_id = $split_oid[(count($split_oid) - 2)];
            $num_index = $member_id . '.' . $disk_index;
            $oid = $disks_base_oid . $num_index;
            $extra = snmp_get($device, $oid, '-OQne', 'EQLDISK-MIB', 'equallogic');
            d_echo($extra);
            if (! empty($extra)) {
                [$foid,$pstatus] = explode(' = ', $extra, 2);
                $index = 'eqlDiskStatus.' . $disk_index;
                $low_limit = 0.5;
                $high_limit = 1.5;
                discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, "Disk $disk_index - $descr", 1, 1, $low_limit, $low_limit, $high_limit, $high_limit, $pstatus, 'snmp', $index);
                create_sensor_to_state_index($device, $state_name, $index);
                unset(
                    $index,
                    $low_limit,
                    $high_limit
                );
            }
            unset(
                $split_oid,
                $disk_index,
                $index,
                $member_id,
                $num_index,
                $oid,
                $extra
            );
        }//end if
        unset(
            $data
        );
    }//end foreach
}//end if empty oids

unset(
    $oid_disks,
    $disks_base_oid,
    $disks_state_name,
    $disks_state_index_id,
    $disk_states,
    $insert
);
