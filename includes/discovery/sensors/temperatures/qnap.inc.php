<?php

if ($device['os'] == 'qnap') {
    echo 'QNAP: ';

    // Turbo NAS Temperature
    $turbonas_temperature_oid = '.1.3.6.1.4.1.24681.1.3.6.0';
    // Turbo NAS Disk Temperature
    $disk_temperature_oid = '.1.3.6.1.4.1.24681.1.2.11.1.3.';


    // Get Turbo NAS temperature
    $turbonas_temperature = snmp_get($device, $turbonas_temperature_oid, '-Oqv');
    // Save the Turbo NAS temperature
    discover_sensor($valid['sensor'], 'temperature', $device, $turbonas_temperature_oid, '99', 'snmp', 'System Temperature', '1', '1', null, null, null, null, $turbonas_temperature);


    // Get all disks in the device
    $disks = snmpwalk_cache_multi_oid($device, 'SystemHdTable', array(), 'NAS-MIB');
    // Parse all disks in the device to get the temperatures
    if (is_array($disks)) {
        foreach ($disks as $disk_number => $entry) {
            // Get the disk temperature full oid
            $disk_oid = $disk_temperature_oid.$disk_number;
            // Get the temperature for the disk
            $disk_temperature = $entry['HdTemperature'];
            // Getting the disk information (Number and model)
            $disk_information = $entry['HdDescr'].' '.$entry['HdModel'];
            // Save the temperature for the disk
            discover_sensor($valid['sensor'], 'temperature', $device, $disk_oid, $disk_number, 'snmp', $disk_information, '1', '1', null, null, null, null, $disk_temperature);
        }
    }
}
