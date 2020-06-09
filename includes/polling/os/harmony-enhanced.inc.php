<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$harmony_data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.7262.4.5.2.4.2.1.1.3.1', '.1.3.6.1.4.1.7262.4.5.2.4.1.3.0']);
$version      = $harmony_data['.1.3.6.1.4.1.7262.4.5.2.4.2.1.1.3.1'];
$serial       = $harmony_data['.1.3.6.1.4.1.7262.4.5.2.4.1.3.0'];
