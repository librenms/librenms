<?php

/*
 * LibreNMS Sensor pre-cache module for Brocade IronWare Interface dBm
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Caching Brocade IronWare Optics';
$pre_cache['ironware_optic_oids'] = snmpwalk_cache_multi_oid($device, 'snIfOpticalMonitoringInfoTable', [], 'FOUNDRY-SN-SWITCH-GROUP-MIB');
