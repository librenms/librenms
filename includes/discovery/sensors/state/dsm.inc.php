<?php

echo 'DSM States';

// System Status (Value : 1 Normal, 2 Failed)
$state = snmp_get($device, "systemStatus.0", "-Ovqe", 'SYNOLOGY-SYSTEM-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.1.1.0';
$index = '0';

if (is_numeric($state)) {
    //Create State Index
    $state_name = 'systemStatusState';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Failed'],
    ];
    create_state_index($state_name, $states);

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'System Status', 1, 1, null, null, null, null, $state, 'snmp', $index);

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
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Failed'],
    ];
    create_state_index($state_name, $states);

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Power Status', 1, 1, null, null, null, null, $state, 'snmp', $index);

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
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Failed'],
    ];
    create_state_index($state_name, $states);


    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'System Fan Status', 1, 1, null, null, null, null, $state, 'snmp', $index);

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
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Failed'],
    ];
    create_state_index($state_name, $states);

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'CPU Fan Status', 1, 1, null, null, null, null, $state, 'snmp', $index);

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
    $states = [
        ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Available'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Unavailable'],
        ['value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'Connecting'],
        ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'Disconnected'],
        ['value' => 5, 'generic' => 3, 'graph' => 0, 'descr' => 'Others'],
    ];
    create_state_index($state_name, $states);

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid, $index, $state_name, 'Upgrade Availability', 1, 1, null, null, null, null, $state, 'snmp', $index);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}

// RAID Status OID (Value : 1 Normal, 2 Repairing, 3 Migrating, 4 Expanding, 5 Deleting, 6 Creating, 7 RaidSyncing, 8 RaidParityChecking, 9 RaidAssembling, 10 Canceling, 11 Degrade, 12 Crashed, 13 DataScrubbing)
$oids = snmpwalk_cache_multi_oid($device, 'raidTable', [], 'SYNOLOGY-RAID-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.3.1.1.3.';

if (is_array($oids)) {
    //Create State Index
    $state_name = 'raidStatusState';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Repairing'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Migrating'],
        ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'Expanding'],
        ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'Deleting'],
        ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'Creating'],
        ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'RaidSyncing'],
        ['value' => 8, 'generic' => 1, 'graph' => 0, 'descr' => 'RaidParityChecking'],
        ['value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'RaidAssembling'],
        ['value' => 10, 'generic' => 1, 'graph' => 0, 'descr' => 'Canceling'],
        ['value' => 11, 'generic' => 2, 'graph' => 0, 'descr' => 'Degrade'],
        ['value' => 12, 'generic' => 2, 'graph' => 0, 'descr' => 'Crashed'],
        ['value' => 13, 'generic' => 1, 'graph' => 0, 'descr' => 'DataScrubbing'],
    ];
    create_state_index($state_name, $states);

    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, 'RAID Status', 1, 1, null, null, null, null, $entry['raidStatus'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}

// Disks Status OID (Value : 1 Normal, 2 Initialized, 3 Not Initialized, 4 System Partition Failed, 5 Crashed )
$oids = snmpwalk_cache_multi_oid($device, 'diskTable', [], 'SYNOLOGY-DISK-MIB');
$cur_oid = '.1.3.6.1.4.1.6574.2.1.1.5.';

if (is_array($oids)) {
    //Create State Index
    $state_name = 'diskStatusState';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Normal'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Initialized'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Not Initialized'],
        ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'System Partition Failed'],
        ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'Crashed'],
    ];
    create_state_index($state_name, $states);
        
    foreach ($oids as $index => $entry) {
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, 'Disk Status '.$index, 1, 1, null, null, null, null, $entry['diskStatus'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
