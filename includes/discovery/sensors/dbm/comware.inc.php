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
$divisor = 100;
$hh3cTransceiverInfoTable = SnmpQuery::cache()->enumStrings()->walk('HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverInfoTable')->table(1);
foreach ($hh3cTransceiverInfoTable as $index => $entry) {
    if (is_numeric($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurRXPower']) && $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurRXPower'] != 2147483647 && isset($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverDiagnostic'])) {
        $interface = get_port_by_index_cache($device['device_id'], $index);
        if ($interface['ifAdminStatus'] != 'up') {
            continue;
        }

        $oid = '.1.3.6.1.4.1.25506.2.70.1.1.1.12.' . $index;
        $limit_low = round(uw_to_dbm($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverRcvPwrLoAlarm'] / 10), 2);
        $warn_limit_low = round(uw_to_dbm($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverRcvPwrLoWarn'] / 10), 2);
        $limit = round(uw_to_dbm($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverRcvPwrHiAlarm'] / 10), 2);
        $warn_limit = round(uw_to_dbm($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverRcvPwrHiWarn'] / 10), 2);
        $current = $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurRXPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $descr = Rewrite::shortenIfName($interface['ifDescr']) . ' Receive Power';
        discover_sensor(null, 'dbm', $device, $oid, 'rx-' . $index, 'comware', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, group: 'transceiver');
    }

    if (is_numeric($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurTXPower']) && $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurTXPower'] != 2147483647 && isset($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverDiagnostic'])) {
        $oid = '.1.3.6.1.4.1.25506.2.70.1.1.1.9.' . $index;
        $limit_low = round(uw_to_dbm($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverPwrOutLoAlarm'] / 10), 2);
        $warn_limit_low = round(uw_to_dbm($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverPwrOutLoWarn'] / 10), 2);
        $limit = round(uw_to_dbm($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverPwrOutHiAlarm'] / 10), 2);
        $warn_limit = round(uw_to_dbm($entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverPwrOutHiWarn'] / 10), 2);
        $current = $entry['HH3C-TRANSCEIVER-INFO-MIB::hh3cTransceiverCurTXPower'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';
        $interface = get_port_by_index_cache($device['device_id'], $index);
        if ($interface['ifAdminStatus'] == 'up') {
            $descr = Rewrite::shortenIfName($interface['ifDescr']) . ' Transmit Power';
            discover_sensor(null, 'dbm', $device, $oid, 'tx-' . $index, 'comware', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $current, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, group: 'transceiver');
        }
    }
}
