<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Steve Calv�rio <https://github.com/Calvario/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'DSM UPS Voltage';

// UPS Device Manufacturer, example return : SNMPv2-SMI::enterprises.6574.4.1.2.0 = STRING: "American Power Conversion"
$ups_device_manufacturer_oid = '.1.3.6.1.4.1.6574.4.1.2.0';
$ups_device_manufacturer = str_replace('"', '', snmp_get($device, $ups_device_manufacturer_oid, '-Oqv'));
// UPS Device Model, example return : SNMPv2-SMI::enterprises.6574.4.1.1.0 = STRING: "Back-UPS RS 900G"
$ups_device_model_oid = '.1.3.6.1.4.1.6574.4.1.1.0';
$ups_device_model = str_replace('"', '', snmp_get($device, $ups_device_model_oid, '-Oqv'));

// UPS Input Voltage Value, example return : SNMPv2-SMI::enterprises.6574.4.4.1.1.0 = Opaque: Float: 234.000000
$ups_input_voltage_oid = '.1.3.6.1.4.1.6574.4.4.1.1.0';
$ups_input_voltage = snmp_get($device, $ups_input_voltage_oid, '-Oqv');
if (is_numeric($ups_input_voltage)) {
    discover_sensor($valid['sensor'], 'voltage', $device, $ups_input_voltage_oid, 'UPSInputVoltageValue', $ups_device_manufacturer . ' ' . $ups_device_model, 'UPS Input Voltage Value', '1', '1', null, null, null, null, $ups_input_voltage);
}

// UPS Input Voltage Nominal, example return : SNMPv2-SMI::enterprises.6574.4.4.1.4.0 = Opaque: Float: 230.000000
$ups_input_voltage_nominal_oid = '.1.3.6.1.4.1.6574.4.4.1.4.0';
$ups_input_voltage_nominal = snmp_get($device, $ups_input_voltage_nominal_oid, '-Oqv');
if (is_numeric($ups_input_voltage_nominal)) {
    discover_sensor($valid['sensor'], 'voltage', $device, $ups_input_voltage_nominal_oid, 'UPSInputVoltageNominal', $ups_device_manufacturer . ' ' . $ups_device_model, 'UPS Input Voltage Nominal', '1', '1', null, null, null, null, $ups_input_voltage_nominal);
}

// UPS Battery Voltage Value, example return : SNMPv2-SMI::enterprises.6574.4.3.2.1.0 = Opaque: Float: 27.000000
$ups_battery_voltage_oid = '.1.3.6.1.4.1.6574.4.3.2.1.0';
$ups_battery_voltage = snmp_get($device, $ups_battery_voltage_oid, '-Oqv');
if (is_numeric($ups_battery_voltage)) {
    discover_sensor($valid['sensor'], 'voltage', $device, $ups_battery_voltage_oid, 'UPSBatteryVoltage', $ups_device_manufacturer . ' ' . $ups_device_model, 'UPS Battery Voltage', '1', '1', null, null, null, null, $ups_battery_voltage);
}

// UPS Battery Voltage Nominal, example return : SNMPv2-SMI::enterprises.6574.4.3.2.2.0 = Opaque: Float: 24.000000
$ups_battery_voltage_nominal_oid = '.1.3.6.1.4.1.6574.4.3.2.2.0';
$ups_battery_voltage_nominal = snmp_get($device, $ups_battery_voltage_nominal_oid, '-Oqv');
if (is_numeric($ups_battery_voltage_nominal)) {
    discover_sensor($valid['sensor'], 'voltage', $device, $ups_battery_voltage_nominal_oid, 'SystemStatus', $ups_device_manufacturer . ' ' . $ups_device_model, 'UPS Battery Voltage Nominal', '1', '1', null, null, null, null, $ups_battery_voltage_nominal);
}
