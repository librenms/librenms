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
$divisor = 1;
foreach ($pre_cache['junos_ifotn_oids'] as $index => $entry) {
    if (is_numeric($entry['jnxoptIfOTNPMCurrentFECBERMantissa'])) {
        $index = substr_replace($index, '', -2);
        $oid = '.1.3.6.1.4.1.2636.3.73.1.3.3.8.1.5.' . $index . '.1';
        $interface = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifIndex`= ? AND `device_id` = ?', [$index, $device['device_id']]);
        $descr = $interface . ' preFEC BER';

        $limit_low = null;
        $warn_limit_low = null;
        $limit = null;
        $warn_limit = null;
        $tmp_exp = $pre_cache['junos_ifotn_oids'][$index . '.1']['jnxoptIfOTNPMCurrentFECBERExponent'];
        $current = ($entry['jnxoptIfOTNPMCurrentFECBERMantissa']) * pow(10, (-$tmp_exp));
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        discover_sensor($valid['sensor'], 'ber', $device, $oid, $index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
