<?php

echo 'ATS Temperature: ';

$descr = 'System';
$oids = [
    'atsMiscellaneousGroupAtsSystemTemperture.0',
    'emdConfigTempHighSetPoint.0',
    'emdConfigTempLowSetPoint.0',
];
$temperature = snmp_get_multi($device, $oids, '-OteQUs', 'ATS-MIB');

if (is_numeric($temperature['0']['atsMiscellaneousGroupAtsSystemTemperture'])) {
    $temperature['0']['high_warn'] = $temperature['0']['emdConfigTempHighSetPoint'] - 5;
    $temperature['0']['low_warn'] = $temperature['0']['emdConfigTempLowSetPoint'] + 5;
    $temperature['0']['oid'] = '.1.3.6.1.4.1.37662.1.2.2.1.1.5.1.0';
    discover_sensor($valid['sensor'], 'temperature', $device, $temperature['0']['oid'], 'atsMiscellaneousGroupAtsSystemTemperture', 'ats', 'System', '1', '1', $temperature['0']['emdConfigTempLowSetPoint'], $temperature['0']['low_warn'], $temperature['0']['high_warn'], $temperature['0']['emdConfigTempHighSetPoint'], $temperature['0']['atsMiscellaneousGroupAtsSystemTemperture']);
}
