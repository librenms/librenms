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
 *tis*/

echo 'JunOS ';

$multiplier = 1;
$divisor    = 1000000000000;
foreach ($pre_cache['junos_ifoptics_oids'] as $index => $entry) {
    if (is_numeric($entry['jnxPMCurDiffGroupDelay'])) {
        $oid = '.1.3.6.1.4.1.2636.3.71.1.2.1.1.2.'.$index;
        $interface = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', array($index, $device['device_id']));
        $descr = $interface . ' DGD';
        
        $limit_low = null;
        $warn_limit_low = null;
        $limit = null;
        $warn_limit = null;
        $current = $entry['jnxPMCurDiffGroupDelay']/$divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'delay', $device, $oid, $index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
