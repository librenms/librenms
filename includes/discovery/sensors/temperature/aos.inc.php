<?php
echo "Checking Chassis Temperature...\n";

$data=[];
$descr       = 'Chassis Temperature';

// OmniStack
$temperature = snmp_get($device, '.1.3.6.1.4.1.89.53.15.1.9.1', '-Oqv');
if ($descr != '' && is_numeric($temperature) && $temperature > '0') {
    discover_sensor($valid['sensor'], 'temperature', $device, '.1.3.6.1.4.1.89.53.15.1.9.1', '1', 'alcatel-device', $descr, '1', '1', null, null, null, null, $temperature);
}

// AOS6 in yaml file

// AOS7+
$temperature = snmp_get($device, "chasCPMAHardwareBoardTemp.209", '-Oqv','ALCATEL-IND1-CHASSIS-MIB', ':mibs/nokia/aos7:mibs'); //chasCPMAHardwareBoardTemp
if ($descr != '' && is_numeric($temperature) && $temperature > '0') {
    $baseoid  = ".1.3.6.1.4.1.6486.801.1.1.1.3.1.1.3.1.8";
    $data=snmpwalk_cache_index($device,'chasCPMAHardwareBoardTemp',[],'ALCATEL-IND1-CHASSIS-MIB','nokia/aos7');
    print_r($data);
    foreach($data['chasCPMAHardwareBoardTemp'] as $index => $value) {
        $descr = 'Chassis '.($index - 208). " Temperature";
        $oid="$baseoid.$index";
        $id= "$chassis.$index";
        echo "$descr: $value\n";
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $id, 'alcatel-lucent', $descr, '1', '1', null, null, null, null, $value, 'snmp');
    }
}
