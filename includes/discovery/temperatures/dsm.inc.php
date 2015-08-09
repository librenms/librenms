<?php

if ($device['os'] == 'dsm') {
    echo 'DSM temperature ';

    // DiskStation Temperature
    $diskstation_temperature_oid = '.1.3.6.1.4.1.6574.1.2.0';
    // DiskStation Disk Temperature
    $disk_temperature_oid = '.1.3.6.1.4.1.6574.2.1.1.6.';


    // Get DiskStation temperature
    $diskstation_temperature = snmp_get($device, $diskstation_temperature_oid, '-Oqv');
    // Save the DiskStation temperature
    discover_sensor($valid['sensor'], 'temperature', $device, $diskstation_temperature_oid, '99', 'snmp', 'System Temperature', '1', '1', null, null, null, null, $diskstation_temperature);


    // Get all disks in the device
    $disks = snmpwalk_cache_multi_oid($device, 'diskTable', array(), 'SYNOLOGY-DISK-MIB');
    // Parse all disks in the device to get the temperature
    if (is_array($disks)) {
        foreach ($disks as $disk_number => $entry) {
            // Get the disk temperature full oid
            $disk_oid = $disk_temperature_oid.$disk_number;
            // Get the temperature for the disk
            $disk_temperature = $entry['diskTemperature'];
            // Getting the disk information (Number and model)
            $disk_information = $entry['diskID'].' '.$entry['diskModel'];
            // Save the temperature for the disk
            discover_sensor($valid['sensor'], 'temperature', $device, $disk_oid, $disk_number, 'snmp', $disk_information, '1', '1', null, null, null, null, $disk_temperature);
        }
    }
}
