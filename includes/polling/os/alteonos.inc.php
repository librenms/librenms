<?php
/**
 * alteonos.inc.php
 *
 * LibreNMS os poller module for RADWARE/Alteon Application Switch
 *
 * Copyright (c) 2017 Simone Fini <tomfordfirst@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$hardware = trim(snmp_get($device, '1.3.6.1.4.1.1872.2.5.1.3.1.6.0', '-OQv', '', ''), '"').' Vers. '.trim(snmp_get($device, '1.3.6.1.4.1.1872.2.5.1.3.1.7.0', '-OQv', '', ''), '"');
$version = trim(snmp_get($device, '1.3.6.1.4.1.1872.2.5.1.1.1.10.0', '-OQv', '', ''), '"');
$features = 'Ver. '.trim(snmp_get($device, '1.3.6.1.4.1.1872.2.5.1.3.3.1.0', '-OQv', '', ''), '"');
$serial = trim(snmp_get($device, '1.3.6.1.4.1.1872.2.5.1.3.1.18.0', '-OQv', '', ''), '"');
