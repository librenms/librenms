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
$options = [];
$options['filter']['type'] = ['=', $tmp_module];
$options['filter']['disabled'] = ['=', 0];
$options['filter']['ignore'] = ['=', 0];
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

// Only collect SNMP data if we have enabled components
if (count($components > 0)) {
    // Let's gather the stats..
    $atNtpAssociationEntry = snmpwalk_group($device, 'atNtpAssociationEntry', 'AT-NTP-MIB');

    // Loop through the components and extract the data.
    foreach ($components as $key => &$array) {
        $peer = $array['peer'];

        // Let's make sure the rrd is setup for this class.
        $rrd_name = ['ntp', $peer];
        $rrd_def = RrdDefinition::make()
            ->addDataset('stratum', 'GAUGE', 0, 16)
            ->addDataset('offset', 'GAUGE', -1000)
            ->addDataset('delay', 'GAUGE', -1000)
            ->addDataset('dispersion', 'GAUGE', -1000);

        $array['stratum'] = $atNtpAssociationEntry[$array['UID']]['atNtpAssociationStratum'];
        // Set the status, 16 = Bad
        if ($array['stratum'] == 16) {
            $array['status'] = 2;
            $array['error'] = 'NTP is not in sync';
        } else {
            $array['status'] = 0;
            $array['error'] = '';
        }

        // Extract the statistics and update rrd
        $rrd['stratum'] = $array['stratum'];
        $rrd['offset'] = $atNtpAssociationEntry[$array['UID']]['atNtpAssociationOffset'];
        $rrd['offset'] = str_replace(' milliseconds', '', $rrd['offset']);
        $rrd['offset'] = $rrd['offset'] / 1000; // Convert to seconds
        $rrd['delay'] = $atNtpAssociationEntry[$array['UID']]['atNtpAssociationDelay'];
        $rrd['delay'] = str_replace(' milliseconds', '', $rrd['delay']);
        $rrd['delay'] = $rrd['delay'] / 1000; // Convert to seconds
        $rrd['dispersion'] = $atNtpAssociationEntry[$array['UID']]['atNtpAssociationDisp'];
        $tags = compact('ntp', 'rrd_name', 'rrd_def', 'peer');
        data_update($device, 'ntp', $tags, $rrd);

        // Let's print some debugging info.
        d_echo("\n\nComponent: " . $key . "\n");
        d_echo('    Index:      ' . $array['UID'] . "\n");
        d_echo('    Peer:       ' . $array['peer'] . ':' . $array['port'] . "\n");
        d_echo('    Stratum:    atNtpAssociationStratum.' . $array['UID'] . '  = ' . $rrd['stratum'] . "\n");
        d_echo('    Offset:     atNtpAssociationOffset.' . $array['UID'] . ' = ' . $rrd['offset'] . "\n");
        d_echo('    Delay:      atNtpAssociationDelay.' . $array['UID'] . ' = ' . $rrd['delay'] . "\n");
        d_echo('    Dispersion: atNtpAssociationDisp.' . $array['UID'] . ' = ' . $rrd['dispersion'] . "\n");

        // Clean-up after yourself!
        unset($filename, $rrd_filename, $rrd);
    } // End foreach components

    // Write the Components back to the DB.
    $component->setComponentPrefs($device['device_id'], $components);
} // end if count components

// Clean-up after yourself!
unset($type, $components, $component, $options, $tmp_module);
