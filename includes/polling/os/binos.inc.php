<?php

/*
 * LibreNMS Telco Systems OS polling module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

 $version = snmp_get($device, '.1.3.6.1.4.1.738.1.111.1.1.4.0', '-Ovqs', '');
 $serial = snmp_get($device, '.1.3.6.1.4.1.738.1.5.100.1.3.1.0', '-Ovqs', '');
 $hardware = snmp_get($device, '.1.3.6.1.4.1.738.1.5.100.1.3.2.0', '-OQv', '');
 $features = '';

 $version = str_replace('"', '', $version);
 $serial = str_replace('"', '', $serial);
 $hardware = str_replace('"', '', $hardware);
