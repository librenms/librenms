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

$multiplier = 1000000;
$divisor    = 1;
foreach ($pre_cache['junos_ifoptics_oids'] as $index => $entry) {
    if (is_numeric($entry['jnxPMCurCarFreqOffset'])) {
        $oid = '.1.3.6.1.4.1.2636.3.71.1.2.1.1.43.'.$index;
        $interface = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', array($index, $device['device_id']));
        $descr = $interface . ' Carrier Freq Offset';
        # create ifoptioc_aternative index et-0/0/0 eq 1.1.1.1
        $t = explode('/', $interface, 3);
        $t0 = explode('-', $t[0], 2);
        $t1 = $t0[1] + 1;
        $t2 = $t[1] + 1;
        $t3 = $t[2] + 1;
        $alt_index = '1.' . $t1 . '.' . $t2 . '.' . $t3;

        $limit_low = $pre_cache['junos_ifoptics2_oids'][$alt_index]['jnxCarFreqOffsetLowThresh']*$multiplier;
        # $warn_limit_low = $entry['jnxDomCurrentRxLaserPowerLowWarningThreshold']/$divisor;
        $warn_limit_low = null;
        $limit = $pre_cache['junos_ifoptics2_oids'][$alt_index]['jnxCarFreqOffsetHighThresh']*$multiplier;
        # $warn_limit = $entry['jnxDomCurrentRxLaserPowerHighWarningThreshold']/$divisor;
        $warn_limit = null;
        $current = $entry['jnxPMCurCarFreqOffset']*$multiplier;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'frequency', $device, $oid, $index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
