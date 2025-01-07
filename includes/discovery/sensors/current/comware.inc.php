<?php

use LibreNMS\Util\Rewrite;

/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
*/
echo 'Comware ';

$multiplier = 1;
$divisor = 100000;
$divisor_alarm = 1000000;
$hh3cTransceiverInfoTable = SnmpQuery::cache()->enumStrings()->walk('HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverInfoTable')->table(1);
foreach ($hh3cTransceiverInfoTable as $index => $entry) {
    if (is_numeric($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasCurrent']) && $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasCurrent'] != 2147483647 && isset($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverDiagnostic'])) {
        $interface = get_port_by_index_cache($device['device_id'], $index);
        if ($interface['ifAdminStatus'] != 'up') {
            continue;
        }

        $oid = '.1.3.6.1.4.1.25506.2.70.1.1.1.17.' . $index;
        $limit_low = $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasLoAlarm'] / $divisor_alarm;
        $warn_limit_low = $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasLoWarn'] / $divisor_alarm;
        $limit = $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasHiAlarm'] / $divisor_alarm;
        $warn_limit = $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasHiWarn'] / $divisor_alarm;
        $current = $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverBiasCurrent'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';

        $descr = Rewrite::shortenIfName($interface['ifDescr']) . ' Bias Current';
        discover_sensor(null, 'current', $device, $oid, 'bias-' . $index, 'comware', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, group: 'transceiver');
    }
}
