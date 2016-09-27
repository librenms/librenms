<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
if (preg_match('/Version ([^,]+)/', $poll_device['sysDescr'], $regexp_result)) {
    $version = $regexp_result[1];
}

$serial   = snmp_get($device, "ENTITY-MIB::entPhysicalSerialNum.10", "-Osqnv");
if ($serial === 'notForwarding') {
    unset($serial);
}
$hardware = $poll_device['sysObjectID'];
if ($hardware === 'enterprises.8072.3.2.10') {
    unset($hardware);
}
