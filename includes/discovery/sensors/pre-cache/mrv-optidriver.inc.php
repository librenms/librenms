<?php

/*
 * LibreNMS Sensor pre-cache module for the MRV® OptiDriver® Optical Transport Platform
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'nbsCmmcPortTable ';
$pre_cache['mrv-optidriver_oids'] = snmpwalk_cache_multi_oid($device, 'nbsCmmcPortTable', array(), 'NBS-CMMC-MIB');
