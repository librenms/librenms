<?php

/*
 * LibreNMS Sensor pre-cache module for the CradlePoint WiPipe
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Caching WIPIPE-MIB';
$pre_cache['wipipe_oids'] = snmpwalk_cache_multi_oid($device, 'mdmEntry', [], 'WIPIPE-MIB');
