<?php
/*
 * LibreNMS module to capture statistics from the AT-NTP-MIB
 *
 * Copyright (c) 2018 Matt Read <matt.read@alliedtelesis.co.nz>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

$tmp_module = 'ntp';

$component = new LibreNMS\Component();
$options = array();
$options['filter']['type'] = array('=',$tmp_module);
$options['filter']['disabled'] = array('=',0);
$options['filter']['ignore'] = array('=',0);
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

// Only collect SNMP data if we have enabled components
if (count($components > 0)) {
    // Let's gather the stats..
    $cntp = snmpwalk_group($device, 'cntp', 'TEMPUSLXUNISON-MIB');
    foreach ($components as $key => &$array) {
        $cntpRxPkts = $array['cntpRxPkts'];
        $cntpTxPkts = $array['cntpTxPkts'];

        $array['stratum'] = $cntp[$array['UID']]['cntpStratum'];
        // Set the status, 16 = Bad
        if ($array['stratum'] == 16) {
            $array['status'] = 2;
            $array['error'] = 'NTP is not in sync';
        } else {
            $array['status'] = 0;
            $array['error'] = '';
        }

        $rrd['stratum'] = $array['stratum'];
        $rrd['cntpTxPkts'] = $array['cntpTxPkts'];
        $rrd['cntpRxPkts'] = $array['cntpRxPkts'];

        // Let's print some debugging info.
        d_echo("\n\nComponent: ".$key."\n");
        d_echo("    NTP RxPackets:      ".$array['cntpRxPkts']."\n");
        d_echo("    NTP TxPackets:      ".$array['cntpTxPkts']."\n");
        d_echo("    Stratum:    cntpStratum.".$array['UID']."  = ".$rrd['stratum']."\n");

        // Clean-up after yourself!
        unset($filename, $rrd_filename, $rrd);
    } // End foreach components
} // end if count components
// Clean-up after yourself!
unset($type, $components, $component, $options, $tmp_module);
