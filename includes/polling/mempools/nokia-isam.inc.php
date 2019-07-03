<?php

/*
 * LibreNMS NOKIA ISAM RAM polling module
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/
echo 'Nokia ISAM Memory: ';

$oid = $mempool['mempool_index'];

$oids = array(
    "memAbsoluteUsage.$oid",
    "totalMemSize.$oid",
);
$data = snmp_get_multi_oid($device, $oids, '-OUQ', 'ASAM-SYSTEM-MIB');

list($mempool['used'], $mempool['total']) = array_values($data);
$mempool['free'] = ($mempool['total'] + $mempool['used']);
