<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2015 Steve Calvário <https://github.com/Calvario/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


if ($device['os'] == 'dsm') {

	echo "DSM temperature ";

	// DiskStation Temperature
	$diskstation_temperature_oid = '.1.3.6.1.4.1.6574.1.2.0';
	// DiskStation Disk Temperature
	$disk_temperature_oid = '.1.3.6.1.4.1.6574.2.1.1.6.';


	// Get DiskStation temperature
	$diskstation_temperature = snmp_get($device, $diskstation_temperature_oid, "-Oqv");
	// Save the DiskStation temperature
	discover_sensor($valid['sensor'], 'temperature', $device, $diskstation_temperature_oid, '2', 'snmp', 'System Temperature', '1', '1', NULL, NULL, NULL, NULL, $diskstation_temperature);


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
			$disk_information = $entry['diskID'] . ' ' . $entry['diskModel'];
			// Save the temperature for the disk
			discover_sensor($valid['sensor'], 'temperature', $device, $disk_oid, $disk_number, 'snmp', $disk_information, '1', '1', NULL, NULL, NULL, NULL, $disk_temperature);
        }
    }
}
