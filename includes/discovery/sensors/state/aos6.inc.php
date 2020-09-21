<?php

$chas_oid = '.1.3.6.1.4.1.6486.800.1.1.1.1.1.1.1.2.'; // chasEntPhysOperStatus
$oids = snmp_walk($device, 'chasEntPhysOperStatus', '-OQUse', 'ALCATEL-IND1-CHASSIS-MIB', 'nokia');
foreach (explode("\n", $oids) as $chas_entry) {
    preg_match('/chasEntPhysOperStatus.(.) = (.+)/', $chas_entry, $data2); // entPhysicalName.284 = "5/PS-2"
    if (! empty($data2)) {
        $number = $data2[1];
        $value = $data2[2];
        $chas_oid_index = $chas_oid . $number;
        $chas_current = "chasEntPhysOperStatus.$number";
        $descr_oid = '.1.3.6.1.2.1.47.1.1.1.1.7.' . $number;
        $chas_descr = snmp_get($device, $descr_oid, '-Oqv');
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
        ];
        create_state_index($chas_state_name, $chas_states);
        discover_sensor($valid['sensor'], 'state', $device, $chas_oid_index, $number, $chas_state_name, $chas_descr, 1, 1, null, null, null, null, $value);
        create_sensor_to_state_index($device, $chas_state_name, $number);
    }
}

unset(
    $index,
    $data,
    $descr
);

foreach ($pre_cache['aos6_fan_oids'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.6486.800.1.1.1.3.1.1.11.1.2.' . $index;
        $state_name = 'alaChasEntPhysFanStatus';
        $current = $data['alaChasEntPhysFanStatus'];
        [$revindex, $revchass, $revdata,] = explode('.', strrev($oid), 4);
        $chassis = strrev($revchass);
        $indexName = strrev($revindex);
        $descr = 'Chassis-' . ($chassis - 568) . " Fan $indexName";
        $states = [
            ['value' => 0, 'generic' => 1, 'graph' => 1, 'descr' => 'noStatus'],
            ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'notRunning'],
            ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'running'],
        ];
        create_state_index($state_name, $states);
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current);
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
