<?php

$aos6_fan_oids = snmpwalk_cache_multi_oid($device, 'alaChasEntPhysFanTable', [], 'ALCATEL-IND1-CHASSIS-MIB', 'aos6', '-OQUse');
foreach ($aos6_fan_oids as $index => $data) {
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
        if (! empty($current)) {
            create_state_index($state_name, $states);
            discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current);
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}

