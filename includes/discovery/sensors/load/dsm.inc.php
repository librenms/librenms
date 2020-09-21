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

echo 'DSM UPS Load';

// UPS Device Manufacturer, example return : SNMPv2-SMI::enterprises.6574.4.1.2.0 = STRING: "American Power Conversion"
$ups_device_manufacturer_oid = '.1.3.6.1.4.1.6574.4.1.2.0';
$ups_device_manufacturer = str_replace('"', '', snmp_get($device, $ups_device_manufacturer_oid, '-Oqv'));
// Too long name for APC
if ($ups_device_manufacturer == 'American Power Conversion') {
    $ups_device_manufacturer = 'APC';
}

// UPS Device Model, example return : SNMPv2-SMI::enterprises.6574.4.1.1.0 = STRING: "Back-UPS RS 900G"
$ups_device_model_oid = '.1.3.6.1.4.1.6574.4.1.1.0';
$ups_device_model = str_replace('"', '', snmp_get($device, $ups_device_model_oid, '-Oqv'));

// UPS Load Value, example return : SNMPv2-SMI::enterprises.6574.4.2.12.1.0 = Opaque: Float: 4.000000
$ups_load_oid = '.1.3.6.1.4.1.6574.4.2.12.1.0';
$ups_load = snmp_get($device, $ups_load_oid, '-Oqv');
if (is_numeric($ups_load)) {
    discover_sensor($valid['sensor'], 'load', $device, $ups_load_oid, 0, 'snmp', $ups_device_manufacturer . ' ' . $ups_device_model . ' - UPS Load', '1', '1', 0, null, null, 100, intval($ups_load));
}
