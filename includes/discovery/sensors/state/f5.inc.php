<?php

// Power Status OID (Value : 0 Bad, 1 Good, 2 NotPresent)
$temp = snmpwalk_cache_multi_oid($device, 'sysChassisPowerSupplyTable', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp)) {
    echo 'F5 power supply: ';
    //Create State Index
    $state_name = 'sysChassisPowerSupplyStatus';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
            array($state_index_id,'Bad',0,0,2) ,
            array($state_index_id,'Good',0,1,0) ,
            array($state_index_id,'NotPresent',0,2,3),
        );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],         // Value polled from device
                'state_generic_value' => $value[4],  // Set value based on the Nagios standard 0=OK, 1=Warning, 2=Critical, 3=Unknown
            );
            dbInsert($insert, 'state_translations');
        }
    }

    foreach ($temp as $index => $data) {
        $descr      = "sysChassisPowerSupplyStatus.".$temp[$index]['sysChassisPowerSupplyIndex'];
        $current    = $data['sysChassisPowerSupplyStatus'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.3.2.2.2.1.2.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))

// FailOver State
/*  From the MIB file:
sysCmFailoverStatusId OBJECT-TYPE
    SYNTAX INTEGER {
        unknown(0), offline(1), forcedOffline(2), standby(3), active(4)
    }
    MAX-ACCESS read-only
    STATUS current
    DESCRIPTION
        "The failover status ID on the system.
        unknown - the failover status of the device is unknown;
        offline - the device is offline;
        forcedOffline - the device is forced offline;
        standby - the device is standby;
        active - the device  is active."
        ::= { sysCmFailoverStatus 1 }
*/

$temp1 = snmpwalk_cache_multi_oid($device, 'sysCmFailoverStatus', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp1)) {
    echo 'F5 FailOver State: ';
    //Create State Index
    $state_name = 'sysCmFailoverStatusId';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
            array($state_index_id,'Unknown',0,0,3),
            array($state_index_id,'OffLine',0,1,2),
            array($state_index_id,'ForcedOffline',0,2,2),
            array($state_index_id,'Standby',0,3,1),
            array($state_index_id,'Active',0,4,0),
        );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],         // Value polled from device
                'state_generic_value' => $value[4],  // Set value based on the Nagios standard 0=OK, 1=Warning, 2=Critical, 3=Unknown
            );
            dbInsert($insert, 'state_translations');
        }
    }

    foreach (array_keys($temp1) as $index) {
        $descr      = "sysCmFailoverStatusId.".$temp1[$index]['sysCmFailoverStatusId'];
        $current    = $temp1[$index]['sysCmFailoverStatusId'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.14.3.1.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp1) as $index)
} // End if (is_array($temp1))


// Color State:
/*
sysCmFailoverStatusColor OBJECT-TYPE
    SYNTAX INTEGER {
        green(0),
        yellow(1),
        red(2),
        blue(3),
        gray(4),
        black(5)
    }
    MAX-ACCESS read-only
    STATUS current
    DESCRIPTION
        "The color of the failover status on the system.
        green - the system is functioning correctly;
        yellow - the system may be functioning suboptimally;
        red - the system requires attention to function correctly;
        blue - the system's status is unknown or incomplete;
        gray - the system is intentionally not functioning (offline);
        black - the system is not connected to any peers."
        ::= { sysCmFailoverStatus 3 }
*/

$temp1 = snmpwalk_cache_multi_oid($device, 'sysCmFailoverStatusColor', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp1)) {
    echo 'F5 FailOver State Color: ';
    //Create State Index
    $state_name = 'sysCmFailoverStatusColor';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
            array($state_index_id,'Green: functioning correctly',0,0,0),
            array($state_index_id,'Yellow: functioning suboptimally',0,1,1),
            array($state_index_id,'Red: requires attention to function correctly',0,2,2),
            array($state_index_id,'Blue: status is unknown',0,3,3),
            array($state_index_id,'Gray: intentionally not functioning',0,4,0),
            array($state_index_id,'Black: not connected to any peers',0,5,2),
        );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],         // Value polled from device
                'state_generic_value' => $value[4],  // Set value based on the Nagios standard 0=OK, 1=Warning, 2=Critical, 3=Unknown
            );
            dbInsert($insert, 'state_translations');
        }
    }

    foreach (array_keys($temp1) as $index) {
        $descr      = "sysCmFailoverStatusColor.".$index;
        $current    = $temp1[$index]['sysCmFailoverStatusColor'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.14.3.3.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp1) as $index)
} // End if (is_array($temp1))

$temp3 = snmpwalk_cache_multi_oid($device, 'sysChassisFanStatus', array(), 'F5-BIGIP-SYSTEM-MIB');

if (is_array($temp3)) {
    echo 'F5 FanSpeed State: ';
    //Create State Index
    $state_name = 'sysChassisFanStatus';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
            array($state_index_id,'Bad',0,0,2),
            array($state_index_id,'Good',0,1,0),
            array($state_index_id,'NotPresent',0,2,3),
        );
        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],         // Value polled from device
                'state_generic_value' => $value[4],  // Set value based on the Nagios standard 0=OK, 1=Warning, 2=Critical, 3=Unknown
            );
            dbInsert($insert, 'state_translations');
        }
    }

    foreach (array_keys($temp3) as $index) {
        $descr      = "Fan Speed Status ".$index;
        $current    = $temp3[$index]['sysChassisFanStatus'];
        $sensorType = 'f5';
        $oid        = '.1.3.6.1.4.1.3375.2.1.3.2.1.2.1.2.'.$index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, '1', '1', null, null, null, null, $current, 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp3) as $index)
} // End if (is_array($temp3))
