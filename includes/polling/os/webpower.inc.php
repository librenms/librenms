<?php
/*
 * LibreNMS WebPower OS information module
 *
 * Copyright (c) 2015 Mike Rostermund <mike@kollegienet.dk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$data = str_replace('"', '', snmp_get($device, '1.3.6.1.2.1.33.1.1.4.0', '-Ovq'));
preg_match_all('/^WebPower Pro II Card|v[0-9]+.[0-9]+|(SN [0-9]+)/', $data, $matches);
$hardware = $matches[0][0];
$version = $matches[0][1];
$serial = $matches[0][2];
