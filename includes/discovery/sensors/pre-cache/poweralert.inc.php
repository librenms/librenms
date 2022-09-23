<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Neil Lathwood <neil@lathwood.co.uk>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'tlUpsSnmpCardSerialNum ';
$pre_cache['poweralert_serial'] = trim(snmp_get($device, '.1.3.6.1.4.1.850.100.1.1.4.0', '-Ovq', 'TRIPPLITE-MIB'), '"');
