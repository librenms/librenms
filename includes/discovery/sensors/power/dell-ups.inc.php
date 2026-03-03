<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Dell UPS: ';
$temp = SnmpQuery::get('DELL-SNMP-UPS-MIB::physicalOutputPresentConsumption.0')->value();
if (is_numeric($temp) && ! is_null($temp)) {
    $oid = '.1.3.6.1.4.1.674.10902.2.120.2.6.0';
    $descr = 'System Consumption';
    discover_sensor(null, \LibreNMS\Enum\Sensor::Power, $device, $oid, '0', 'dell-ups', $descr, '1', '1', null, null, null, null, $temp);
}
