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

echo 'DSM UPS Power';

// UPS Device Manufacturer, example return : SNMPv2-SMI::enterprises.6574.4.1.2.0 = STRING: "American Power Conversion"
$ups_device_manufacturer_oid = '.1.3.6.1.4.1.6574.4.1.2.0';
$ups_device_manufacturer = str_replace('"', '', SnmpQuery::get($ups_device_manufacturer_oid)->value());
// UPS Device Model, example return : SNMPv2-SMI::enterprises.6574.4.1.1.0 = STRING: "Back-UPS RS 900G"
$ups_device_model_oid = '.1.3.6.1.4.1.6574.4.1.1.0';
$ups_device_model = str_replace('"', '', SnmpQuery::get($ups_device_model_oid)->value());

// UPS Info Real Power Nominal, example return : SNMPv2-SMI::enterprises.6574.4.2.21.2.0 = Opaque: Float: 540.000000
$ups_real_power_nominal_oid = '.1.3.6.1.4.1.6574.4.2.21.2.0';
$ups_real_power_nominal = SnmpQuery::get($ups_real_power_nominal_oid)->value();
if (is_numeric($ups_real_power_nominal)) {
    discover_sensor(null, \LibreNMS\Enum\Sensor::Power, $device, $ups_real_power_nominal_oid, 'UPSRealPowerNominal', $ups_device_manufacturer . ' ' . $ups_device_model, 'UPS Real Power Nominal', '1', '1', null, null, null, null, $ups_real_power_nominal);
}
