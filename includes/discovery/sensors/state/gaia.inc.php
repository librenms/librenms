<?php
$states = [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Active'],
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'Standby'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'Active Attention'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'Down'],
];

$temp = snmpwalk_cache_multi_oid($device, 'haState', [], 'CHECKPOINT-MIB');

if (is_array($temp)) {
    echo 'Gaia Cluster HA State: ';
    //Create State Index
    $state_name = 'haState';
    create_state_index($state_name, $states);

    foreach ($temp as $index => $data) {
		//Convert string value to integer LibreNMS value
		if ($currentstr == 'active') {
				$value = 0;
		if ($currentstr == 'standby') {
				$value = 1;
		if ($currentstr == 'Active Attention') {
				$value = 2;
		if ($currentstr == 'Down') {
				$value = 3;
		}
		}
		}
		}		
        $descr = 'Cluster HA State';
        $group = 'HA';
        $current = $value;
        $oid = '.1.3.6.1.4.1.2620.1.5.6.' . $index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', $index, null, null, $group);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))

$states = [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Connected'],
        ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'Disconnected'],
];

$temp = snmpwalk_cache_multi_oid($device, 'mglsGWState', [], 'CHECKPOINT-MIB');

if (is_array($temp)) {
    echo 'Gaia Management Connected Gateways: ';
    //Create State Index
    $state_name = 'mglsGWState';
    create_state_index($state_name, $states);

    foreach ($temp as $index => $data) {
		//Convert string value to integer LibreNMS value
		if ($currentstr == 'Connected') {
				$value = 0;
		if ($currentstr == 'Disconnected') {
				$value = 1;
		}
		}
		$mglsGWIP = snmp_get($device, 'mglsGWIP.' . $index, '-Ovq', 'CHECKPOINT-MIB');		
        $descr = $mglsGWIP;
        $group = 'Management Connected Gateways';
        $current = $value;
        $oid = '.1.3.6.1.4.1.2620.1.7.14.4.1.3.' . $index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', $index, null, null, $group);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))

$states = [
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Disabled'],
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Enabled'],
];

$temp = snmpwalk_cache_multi_oid($device, 'fwSXLStat', [], 'CHECKPOINT-MIB');

if (is_array($temp)) {
    echo 'SecureXL current status: ';
    //Create State Index
    $state_name = 'fwSXLStat';
    create_state_index($state_name, $states);

    foreach ($temp as $index => $data) {
        $descr = 'SecureXL current status';
        $group = 'HA';
        $current = $data['fwSXLStat'];
        $oid = '.1.3.6.1.4.1.2620.1.36.1.1.' . $index;
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $current, 'snmp', $index, null, null, $group);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    } // End foreach (array_keys($temp) as $index)
} // End if (is_array($temp))
