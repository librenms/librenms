<?php

foreach ($pre_cache['aos7_fan_oids'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.6486.801.1.1.1.3.1.1.11.1.2.' . $index;
        $state_name = 'alaChasEntPhysFanStatus';
        $current = $data['alaChasEntPhysFanStatus'];
        [$revindex, $revchass, $revdata,] = explode('.', strrev($oid), 4);
        $chassis = strrev($revchass);
        $indexName = strrev($revindex);
        $descr_oid = '.1.3.6.1.2.1.47.1.1.1.1.7.' . $chassis;
        $chas_descr = (string) snmp_get($device, $descr_oid, '-Oqv');
        $descr = 'CHASSIS-' . substr($chas_descr, 0, strpos($chas_descr, '/')) . " Fan $indexName";
        $states = [
            ['value' => 0, 'generic' => 2, 'graph' => 1, 'descr' => 'no-error'],
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'notRunning'],
            ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'running'],
        ];
        create_state_index($state_name, $states);
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current);
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
unset(
    $index,
    $data,
    $descr
);

$chas_oid = '.1.3.6.1.4.1.6486.801.1.1.1.1.1.1.1.2.'; // chasEntPhysOperStatus
$oids = snmp_walk($device, 'chasEntPhysOperStatus', '-OQUse', 'ALCATEL-IND1-CHASSIS-MIB', 'nokia/aos7');
foreach (explode("\n", $oids) as $chas_entry) {
    preg_match('/chasEntPhysOperStatus.(2.+) = (.+)/', $chas_entry, $data2); // entPhysicalName.284 = "5/PS-2"
    if (! empty($data2)) {
        $number = $data2[1];
        $value = $data2[2];
        $chas_oid_index = $chas_oid . $number;
        $chas_current = "chasEntPhysOperStatus.$number";
        $descr_oid = '.1.3.6.1.2.1.47.1.1.1.1.7.' . $number;
        $chas_descr = (string) snmp_get($device, $descr_oid, '-Oqv');
        $chas_state_name = 'chasEntPhysOperStatus';
        $chas_states = [
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Up'],
            ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'Down'],
            ['value' => 3, 'generic' => 3, 'graph' => 1, 'descr' => 'Testing'],
            ['value' => 4, 'generic' => 3, 'graph' => 1, 'descr' => 'Unknown'],
            ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => 'Secondary'],
            ['value' => 6, 'generic' => 2, 'graph' => 1, 'descr' => 'NotPresent'],
            ['value' => 7, 'generic' => 2, 'graph' => 1, 'descr' => 'UnPowered'],
            ['value' => 8, 'generic' => 0, 'graph' => 1, 'descr' => 'Master'],
            ['value' => 9, 'generic' => 0, 'graph' => 1, 'descr' => 'Idle'],
            ['value' => 10, 'generic' => 0, 'graph' => 1, 'descr' => 'PwrSave'],
        ];
        create_state_index($chas_state_name, $chas_states);
        discover_sensor($valid['sensor'], 'state', $device, $chas_oid_index, $number, $chas_state_name, $chas_descr, 1, 1, null, null, null, null, $value);
        create_sensor_to_state_index($device, $chas_state_name, $number);
    }
}
unset(
    $index,
    $data,
    $descr,
    $states,
    $current
);

$vc_current = [];
foreach ($pre_cache['aos7_vcstack_oids'] as $index => $data) {
    if (is_array($data)) {
        $oid = ".1.3.6.1.4.1.6486.801.1.2.1.69.1.1.7.1.3.$index";
        $index = preg_match('/^(.*)[.]([^.]+)$/', $index, $data2);
        if (! empty($data2)) {
            $number = $data2[1];
            $index = $data2[2];
            $state_name_vc = 'virtualChassisVflMemberPortOperStatus';
            $vc_current = "virtualChassisVflMemberPortOperStatus.$number";
            $entPhysicalIndex = $index;
            $entPhysicalIndex_measured = 'ports';
            $port_descr = get_port_by_index_cache($device['device_id'], $index);
            $descr = 'Virtual-Chassis Port ' . $port_descr['ifName'];
            $states = [
                ['value' => 0, 'generic' => 1, 'graph' => 1, 'descr' => 'Disabled'],
                ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'Down'],
                ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Up'],
            ];
            create_state_index($state_name_vc, $states);
            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name_vc, $descr, 1, 1, null, null, null, null, $vc_current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
            create_sensor_to_state_index($device, $state_name_vc, $index);
        }
    }
}

unset(
    $index,
    $data,
    $descr,
    $states,
    $current
);

foreach ($pre_cache['aos7_vcstatus_oids'] as $index => $data) {
    if (is_array($data)) {
        $vc_chas_oid = '.1.3.6.1.4.1.6486.801.1.2.1.69.1.1.2.1.5.' . $index;
        $vc_state = 'virtualChassisStatus';
        $vc_value = "virtualChassisStatus.$index";
        $descr_vc = 'Virtual-Chassis Unit ' . $index;
        $vc_chas_states = [
            ['value' => 0, 'generic' => 0, 'graph' => 1, 'descr' => 'Init'],
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Running'],
            ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'Invalid Chassis Id'],
            ['value' => 3, 'generic' => 2, 'graph' => 1, 'descr' => 'Hello Down'],
            ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'Duplicate Chassis Id'],
            ['value' => 5, 'generic' => 2, 'graph' => 1, 'descr' => 'Mismatch Image'],
            ['value' => 6, 'generic' => 2, 'graph' => 1, 'descr' => 'Mismatch Chassis Type'],
            ['value' => 7, 'generic' => 2, 'graph' => 1, 'descr' => 'Mismatch Hello Interval'],
            ['value' => 8, 'generic' => 2, 'graph' => 1, 'descr' => 'Mismatch Control Vlan'],
            ['value' => 9, 'generic' => 2, 'graph' => 1, 'descr' => 'Mismatch Group'],
            ['value' => 10, 'generic' => 2, 'graph' => 1, 'descr' => 'Mismatch License Config'],
            ['value' => 11, 'generic' => 2, 'graph' => 1, 'descr' => 'Invalid License'],
            ['value' => 12, 'generic' => 2, 'graph' => 1, 'descr' => 'Split Topology'],
            ['value' => 13, 'generic' => 2, 'graph' => 1, 'descr' => 'Command Shutdown'],
            ['value' => 14, 'generic' => 2, 'graph' => 1, 'descr' => 'Failure Shutdown'],
        ];
        create_state_index($vc_state, $vc_chas_states);
        discover_sensor($valid['sensor'], 'state', $device, $vc_chas_oid, $index, $vc_state, $descr_vc, 1, 1, null, null, null, null, $vc_value);
        create_sensor_to_state_index($device, $vc_state, $index);
    }
}

unset(
    $index,
    $data,
    $descr,
    $states,
    $current
);

foreach ($pre_cache['aos7_sync_oids'] as $index => $data) {
    if (is_array($data)) {
        $sync_chas_oid = '.1.3.6.1.4.1.6486.801.1.1.1.3.1.1.1.1.4.' . $index;
        $sync_state = 'chasControlCertifyStatus';
        $sync_value = 'chasControlCertifyStatus.1';
        $descr_sync = 'Certify/Restore Status';
        $sync_chas_states = [
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Unknown'],
            ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'Need Certify'],
            ['value' => 3, 'generic' => 0, 'graph' => 1, 'descr' => 'Certified'],
        ];
        create_state_index($sync_state, $sync_chas_states);
        discover_sensor($valid['sensor'], 'state', $device, $sync_chas_oid, 1, $sync_state, $descr_sync, 1, 1, null, null, null, null, $sync_value);
        create_sensor_to_state_index($device, $sync_state, 1);
    }
}

unset(
    $sync_chas_oid,
    $sync_state,
    $sync_value,
    $descr_sync,
    $sync_chas_states,
    $data,
    $index
);
foreach ($pre_cache['aos7_sync_oids'] as $index => $data) {
    if (is_array($data)) {
        $sync_chas_oid = '.1.3.6.1.4.1.6486.801.1.1.1.3.1.1.1.1.5.' . $index;
        $sync_state = 'chasControlSynchronizationStatus';
        $sync_value = 'chasControlSynchronizationStatus.1';
        $descr_sync = 'Flash Between CMMs';
        $sync_chas_states = [
            ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'Unknown'],
            ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'Mono Control Module'],
            ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'Not Synchronized'],
            ['value' => 4, 'generic' => 0, 'graph' => 1, 'descr' => 'Synchronized'],
        ];
        create_state_index($sync_state, $sync_chas_states);
        discover_sensor($valid['sensor'], 'state', $device, $sync_chas_oid, 1, $sync_state, $descr_sync, 1, 1, null, null, null, null, $sync_value);
        create_sensor_to_state_index($device, $sync_state, 1);
    }
}
unset(
    $sync_chas_oid,
    $sync_state,
    $sync_value,
    $descr_sync,
    $sync_chas_states,
    $data,
    $index
);

$type = 'alclnkaggAggNbrAttachedPorts';
foreach ($pre_cache['aos7_lag_oids'] as $index => $entry) {
    $oid_size = $entry['alclnkaggAggSize'];
    $oid_mem = $entry['alclnkaggAggNbrAttachedPorts'];
    $lag_state = '.1.3.6.1.4.1.6486.801.1.2.1.13.1.1.1.1.1.19.' . $index;
    // $oid_state = "alclnkaggAggNbrAttachedPorts.$index";
    $lag_number = $entry['alclnkaggAggNumber'];
    if (! empty($oid_mem)) {
        if ($oid_size == $oid_mem) {
            $current = 1;
        }
        if ($oid_size > $oid_mem) {
            $current = 2;
        }
    }
    $descr_lag = 'LACP Number ' . $lag_number;
    $lag_states = [
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Redundant'],
        ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'Not Redundant'],
    ];
    if (! empty($oid_mem)) {
        create_state_index($type, $lag_states);
        discover_sensor($valid['sensor'], 'state', $device, $lag_state, $index, $type, $descr_lag, 1, 1, null, null, null, null, $current);
        create_sensor_to_state_index($device, $type, $index);
    }
}
