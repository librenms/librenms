<?php
/*
 * LibreNMS module to display F5 GTM Wide IP Details
 *
 * Adapted from F5 LTM module by Darren Napper
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

// Define some error messages
$error_poolaction = [];
$error_poolaction[0] = 'Unused';
$error_poolaction[1] = 'Reboot';
$error_poolaction[2] = 'Restart';
$error_poolaction[3] = 'Failover';
$error_poolaction[4] = 'Failover and Restart';
$error_poolaction[5] = 'Go Active';
$error_poolaction[6] = 'None';

$component = new LibreNMS\Component();
$options['filter']['disabled'] = ['=', 0];
$options['filter']['ignore'] = ['=', 0];
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

// We extracted all the components for this device, now lets only get the GTM ones.
$keep = [];
$types = ['f5-gtm-wide', 'f5-gtm-pool'];
foreach ($components as $k => $v) {
    foreach ($types as $type) {
        if ($v['type'] == $type) {
            $keep[$k] = $v;
        }
    }
}
$components = $keep;

// Only collect SNMP data if we have enabled components
if (! empty($components)) {
    // Let's gather the stats..
    $f5_stats['gtmWideIPStatEntryRequests'] = snmpwalk_array_num($device, '.1.3.6.1.4.1.3375.2.3.12.2.3.1.2', 0);
    $f5_stats['gtmWideIPStatEntryResolved'] = snmpwalk_array_num($device, '.1.3.6.1.4.1.3375.2.3.12.2.3.1.3', 0);
    $f5_stats['gtmWideIPStatEntryDropped'] = snmpwalk_array_num($device, '.1.3.6.1.4.1.3375.2.3.12.2.3.1.7', 0);
    $f5_stats['gtmPoolEntryResolved'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.3.6.2.3.1.2', 0);
    $f5_stats['gtmPoolEntryDropped'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.3.6.2.3.1.5', 0);

    // and check the status
    $f5_stats['gtmWideIPStatEntryState'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.3.12.3.2.1.2', 0);
    $f5_stats['gtmWideIPStatEntryMsg'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.3.12.3.2.1.5', 0);

    // Loop through the components and extract the data.
    foreach ($components as $key => &$array) {
        $type = $array['type'];
        $UID = $array['UID'];
        $label = $array['label'];
        $hash = $array['hash'];
        $rrd_name = [$type, $label, $hash];

        if ($type == 'f5-gtm-wide') {
            $rrd_def = RrdDefinition::make()
                ->addDataset('requests', 'COUNTER', 0)
                ->addDataset('resolved', 'COUNTER', 0)
                ->addDataset('dropped', 'COUNTER', 0);

            $fields = [
                'requests' => $f5_stats['gtmWideIPStatEntryRequests']['1.3.6.1.4.1.3375.2.3.12.2.3.1.2.' . $UID],
                'resolved' => $f5_stats['gtmWideIPStatEntryResolved']['1.3.6.1.4.1.3375.2.3.12.2.3.1.3.' . $UID],
                'dropped' => $f5_stats['gtmWideIPStatEntryDropped']['1.3.6.1.4.1.3375.2.3.12.2.3.1.7.' . $UID],
            ];

            // Let's print some debugging info.
            d_echo("\n\nComponent: " . $key . "\n");
            d_echo('    Type: ' . $type . "\n");
            d_echo('    Label: ' . $label . "\n");
        } elseif ($type == 'f5-gtm-pool') {
            $rrd_def = RrdDefinition::make()
                ->addDataset('resolved', 'COUNTER', 0)
                ->addDataset('dropped', 'COUNTER', 0);

            $fields = [
                'resolved' => $f5_stats['gtmPoolEntryResolved']['1.3.6.1.4.1.3375.2.3.6.2.3.1.2.' . $UID],
                'dropped' => $f5_stats['gtmPoolEntryDropped']['1.3.6.1.4.1.3375.2.3.6.2.3.1.5.' . $UID],
            ];

            // Let's print some debugging info.
            d_echo("\n\nComponent: " . $key . "\n");
            d_echo('    Type: ' . $type . "\n");
            d_echo('    Label: ' . $label . "\n");
        } else {
            d_echo('Type is unknown: ' . $type . "\n");
            continue;
        }

        $tags = compact('rrd_name', 'rrd_def', 'type', 'hash', 'label');
        data_update($device, $type, $tags, $fields);
    } // End foreach components

    unset($f5_stats);

    // Write the Components back to the DB.
    $component->setComponentPrefs($device['device_id'], $components);
} // end if count components

// Clean-up after yourself!
unset(
    $type,
    $components,
    $component,
    $options,
    $error_poolaction,
    $keep
);
