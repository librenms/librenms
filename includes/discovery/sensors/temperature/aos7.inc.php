<?php
echo "Checking Chassis Temperature...\n";

$data=[];
$descr = '';

// AOS7+
$temperature = snmp_get($device, "chasCPMAHardwareBoardTemp.209", '-Oqv', 'ALCATEL-IND1-CHASSIS-MIB', ':mibs/nokia/aos7:mibs'); //chasCPMAHardwareBoardTemp
if ($descr == '' && is_numeric($temperature) && $temperature > '0') {
    $baseoid  = ".1.3.6.1.4.1.6486.801.1.1.1.3.1.1.3.1.8";
    $data=snmpwalk_cache_index($device, 'chasCPMAHardwareBoardTemp', [], 'ALCATEL-IND1-CHASSIS-MIB', 'nokia/aos7');
    foreach ($data['chasCPMAHardwareBoardTemp'] as $index => $value) {
        $descr = 'Chassis '.($index - 208). " Temperature";
        echo "$descr: $value\n";
        discover_sensor($valid['sensor'], 'temperature', $device, "$baseoid.$index", $index, 'alcatel-lucent', $descr, '1', '1', null, null, null, null, $value, 'snmp');
    }
}
