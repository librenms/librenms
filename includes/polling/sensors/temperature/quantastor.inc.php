<?php
/*
 * LibreNMS QuantaStor temperature module
 *
 * Copyright (c) 2020 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$snmp_value = snmp_get($device, $sensor['sensor_oid'], '-Ovqn', "QUANTASTOR-SYS-STATS");
preg_match('/([0-9]+)/', $snmp_value, $value);
$sensor_value = trim($value[0]);

