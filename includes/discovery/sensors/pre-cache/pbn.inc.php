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

echo 'ifSfpParameterTable ';
$pre_cache['pbn_oids'] = snmpwalk_cache_multi_oid($device, '.1.3.6.1.4.1.11606.10.9.63.1.7', [], 'NMS-IF-MIB', 'pbn');
