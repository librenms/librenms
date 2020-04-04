<?php
echo "Checking Chassis Temperature...\n";

$data=[];
$descr = '';

// OmniStack
$temperature = snmp_get($device, '.1.3.6.1.4.1.89.53.15.1.9.1', '-Oqv');
if (is_numeric($temperature) && $temperature > '0') {
    $descr = 'Chassis Temperature';
    echo "$descr: $temperature\n";
    discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.89.53.15.1.9.1', '1', 'alcatel-device', $descr, '1', '1', null, null, null, null, $temperature);
}

// AOS6 Removed from yaml
$temperature = snmp_get($device, "chasHardwareBoardTemp.569", '-Oqv', 'ALCATEL-IND1-CHASSIS-MIB'); //chasHardwareBoardTemp
if ($descr == '' && is_numeric($temperature) && $temperature > '0') {
    $baseoid = ".1.3.6.1.4.1.6486.800.1.1.1.3.1.1.3.1.4.";
    $data=snmpwalk_cache_index($device, 'chasHardwareBoardTemp', [], 'ALCATEL-IND1-CHASSIS-MIB');
    foreach ($data['chasHardwareBoardTemp'] as $index => $value) {
        $descr = 'Chassis '.($index - 568). " Temperature";
        echo "$descr: $value\n";
        discover_sensor($valid['sensor'], 'temperature', $device, "$baseoid.$index", $index, 'alcatel-lucent', $descr, '1', '1', null, null, null, null, $value, 'snmp');
    }
}
