<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2023 Rudy Broersma <r.broersma@ctnet.nl>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = snmpwalk_cache_multi_oid($device, 'entPhysicalVendorType', [], 'ENTITY-MIB');

if (! empty($oids)) {
    $arubaPowerSensorOID = 'enterprises.47196.4.1.1.2.1.3'; // ARUBAWIRED-NETWORKING-OID::arubaWiredPowerSensor
    $arubaPowerSensorOperStatusOID = '.1.3.6.1.2.1.99.1.1.1.5.';

    // Create State Index - Values taken from ENTITY-SENSOR-MIB - EntitySensorStatus
    $state_name = 'arubaoscxEnvMonSupplyState';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'unavailable'],
        ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'nonoperational'],
    ];
    create_state_index($state_name, $states);

    foreach ($oids as $index => $entry) {
        if ($entry['entPhysicalVendorType'] == $arubaPowerSensorOID) { // Is physical entity an arubaWiredPowerSensor?
            // Retrieve sensor name
            $descr = snmp_get($device, 'ENTITY-MIB::entPhysicalName.' . $index, '-Ovqe', 'ENTITY-MIB');

            //Discover Sensors
            discover_sensor($valid['sensor'], 'state', $device, $arubaPowerSensorOperStatusOID . $index, $index, $state_name, $descr, '1', '1', null, null, null, 3, null, 'snmp', $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
