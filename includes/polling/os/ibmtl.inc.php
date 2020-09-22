<?php
/*
* LibreNMS
*
* Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation, either version 3 of the License, or (at your
* option) any later version.  Please see LICENSE.txt at the top level of
* the source code distribution for details.
*/

$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.14851.3.1.4.2.0', '-Ovq'), '" ');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.14851.3.1.3.2.0', '-Ovq'), '" ');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.14851.3.1.3.4.0', '-Ovq'), '" ');
