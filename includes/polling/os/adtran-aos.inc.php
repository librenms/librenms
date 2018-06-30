<?php

/*
 * LibreNMS ADTRAN AOS OS Poller module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


$hardware = snmp_get($device, 'adAOSDeviceProductName.0', '-Ovqs', 'ADTRAN-AOSUNIT');
$version = snmp_get($device, 'adAOSDeviceVersion.0', '-Ovqs', 'ADTRAN-AOSUNIT');
$serial = snmp_get($device, 'adAOSDeviceSerialNumber.0', '-Ovqs', 'ADTRAN-AOSUNIT');
$features = '';
