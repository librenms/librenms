<?php

if ($device['os'] == 'hirschmann') {
    echo 'Hirschmann Device: ';

    $descr = 'Temperature';
    $temperature = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmTemperature.0', '-Oqv');
    $temperature_high = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmTempUprLimit.0', '-Oqv');
    $temperature_low = snmp_get($device, 'HMPRIV-MGMT-SNMP-MIB::hmTempLwrLimit.0', '-Oqv');
    $temperature_low_warn = $temperature_low + 10;
    $temperature_high_warn = $temperature_high - 10;

    if ($descr != '' && is_numeric($temperature)) {
        discover_sensor(
            $valid['sensor'],
            'temperature',
            $device,
            'HMPRIV-MGMT-SNMP-MIB::hmTemperature.0',
            '1',
            'hirschmann',
            $descr,
            '1',
            '1',
            $temperature_low,
            $temperature_low_warn,
            $temperature_high_warn,
            $temperature_high,
            $temperature
        );
    }
}
