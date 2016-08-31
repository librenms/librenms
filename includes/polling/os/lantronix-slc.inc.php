<?php

/*
 * LibreNMS Lantronix SLC OS Polling module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

 $hardware = snmp_get($device, 'slcSystemModel.0', '-Ovqs', 'LANTRONIX-SLC-MIB');
 $hardware = str_replace('"', '', $hardware);
 $version = snmp_get($device, 'slcSystemFWRev.0', '-Ovqs', 'LANTRONIX-SLC-MIB');
 $version = str_replace('"', '', $version);
 $serial = snmp_get($device, 'slcSystemSerialNo.0', '-Ovqs', 'LANTRONIX-SLC-MIB');
 $serial = str_replace('"', '', $serial);
 $features = snmp_get($device, 'slcSystemModelString.0', '-Ovqs', 'LANTRONIX-SLC-MIB');
 $features = str_replace('"', '', $features);
