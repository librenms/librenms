<?php

echo 'DSM States';

// System Status (Value : 1 Normal, 2 Failed)
$state = snmp_get($device, "systemStatus.0", "-Ovqe", 'SYNOLOGY-SYSTEM-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.1.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'systemStatusState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Normal',0,1,0) ,
             array($state_index_id,'Failed',0,2,2) ,
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

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'System Status', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

// Power Status OID (Value : 1 Normal, 2 Failed)
$state = snmp_get($device, "powerStatus.0", "-Ovqe", 'SYNOLOGY-SYSTEM-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.1.3.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'powerStatusState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Normal',0,1,0) ,
             array($state_index_id,'Failed',0,2,2) ,
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

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Power Status', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

// System Fan Status OID (Value : 1 Normal, 2 Failed)
$state = snmp_get($device, "systemFanStatus.0", "-Ovqe", 'SYNOLOGY-SYSTEM-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.1.4.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'systemFanStatusState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Normal',0,1,0) ,
             array($state_index_id,'Failed',0,2,2) ,
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


    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'System Fan Status', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

// CPU Fan Status OID (Value : 1 Normal, 2 Failed)
$state = snmp_get($device, "cpuFanStatus.0", "-Ovqe", 'SYNOLOGY-SYSTEM-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.1.4.2.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'cpuFanStatusState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Normal',0,1,0) ,
             array($state_index_id,'Failed',0,2,2) ,
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

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'CPU Fan Status', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

// DSM Upgrade Available OID (Value : 1 Available, 2 Unavailable, 3 Connecting, 4 Disconnected, 5 Others)
$state = snmp_get($device, "upgradeAvailable.0", "-Ovqe", 'SYNOLOGY-SYSTEM-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.1.5.4.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'upgradeAvailableState';
    $state_index_id = create_state_index($state_name);
    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Available',0,1,1) ,
             array($state_index_id,'Unavailable',0,2,0) ,
             array($state_index_id,'Connecting',0,3,3) ,
             array($state_index_id,'Disconnected',0,4,3) ,
             array($state_index_id,'Others',0,5,3) ,
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

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Upgrade Availability', '1', '1', null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

// RAID Status OID (Value : 1 Normal, 2 Repairing, 3 Migrating, 4 Expanding, 5 Deleting, 6 Creating, 7 RaidSyncing, 8 RaidParityChecking, 9 RaidAssembling, 10 Canceling, 11 Degrade, 12 Crashed)
$oids = snmpwalk_cache_multi_oid($device, 'raidTable', array(), 'SYNOLOGY-RAID-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.3.1.1.3.';

if (is_array($oids)) {
    //Create State Index
    $state_name = 'raidStatusState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Normal',0,1,0) ,
             array($state_index_id,'Repairing',0,2,1) ,
             array($state_index_id,'Migrating',0,3,1) ,
             array($state_index_id,'Expanding',0,4,1) ,
             array($state_index_id,'Deleting',0,5,1) ,
             array($state_index_id,'Creating',0,6,1) ,
             array($state_index_id,'RaidSyncing',0,7,1) ,
             array($state_index_id,'RaidParityChecking',0,8,1) ,
             array($state_index_id,'RaidAssembling',0,9,1) ,
             array($state_index_id,'Canceling',0,10,1) ,
             array($state_index_id,'Degrade',0,11,2) ,
             array($state_index_id,'Crashed',0,12,2) ,
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
    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, 'RAID Status', '1', '1', null, null, null, null, $entry['raidStatus'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

// Disks Status OID (Value : 1 Normal, 2 Initialized, 3 Not Initialized, 4 System Partition Failed, 5 Crashed )
$oids = snmpwalk_cache_multi_oid($device, 'diskTable', array(), 'SYNOLOGY-DISK-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.2.1.1.5.';

if (is_array($oids)) {
    //Create State Index
    $state_name = 'diskStatusState';
    $state_index_id = create_state_index($state_name);

    //Create State Translation
    if ($state_index_id) {
        $states = array(
             array($state_index_id,'Normal',0,1,0) ,
             array($state_index_id,'Initialized',0,2,1) ,
             array($state_index_id,'Not Initialized',0,3,1) ,
             array($state_index_id,'System Partition Failed',0,4,2) ,
             array($state_index_id,'Crashed',0,5,2) ,
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
    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, 'Disk Status '.$index, '1', '1', null, null, null, null, $entry['diskStatus'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
