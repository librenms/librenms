<?php
/*
 * LibreNMS module to display F5 LTM Details
 *
 * Copyright (c) 2021 Martin Bergström <martin@bergstr0m.se>
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

// We extracted all the components for this device, now lets only get the LTM ones.
$keep = [];
$types = ['f5-ltm-vs', 'f5-ltm-poolmember'];
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
    $f5_stats['ltmVirtualServStatEntryCurrconns'] = snmpwalk_array_num($device, '.1.3.6.1.4.1.3375.2.2.10.2.3.1.12', 0);
    $f5_stats['ltmPoolMemberStatEntryCurrconns'] = snmpwalk_array_num($device, '.1.3.6.1.4.1.3375.2.2.5.4.3.1.11', 0);

    // and check the status
    $f5_stats['ltmVsStatusEntryState'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.10.13.2.1.2', 0);
    $f5_stats['ltmVsStatusEntryMsg'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.10.13.2.1.5', 0);

    $f5_stats['ltmPoolMbrStatusEntryState'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.6.2.1.5', 0);
    $f5_stats['ltmPoolMbrStatusEntryAvail'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.6.2.1.6', 0);
    $f5_stats['ltmPoolMbrStatusEntryMsg'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.6.2.1.8', 0);

    // Loop through the components and extract the data.
    foreach ($components as $key => &$array) {
        $type = $array['type'];
        $UID = $array['UID'];
        $label = $array['label'];
        $hash = $array['hash'];
        $rrd_name = [$type, $label, $hash, 'currconns'];

        if ($type == 'f5-ltm-vs') {
            $rrd_def = RrdDefinition::make()
                ->addDataset('currconns', 'GAUGE', 0);

            $fields = [
                'currconns' => $f5_stats['ltmVirtualServStatEntryCurrconns']['1.3.6.1.4.1.3375.2.2.10.2.3.1.12.' . $UID],
            ];

            // Let's print some debugging info.
            d_echo("\n\nComponent: " . $key . "\n");
            d_echo('    Type: ' . $type . "\n");
            d_echo('    Label: ' . $label . "\n");

            // Let's check the status.
            $array['state'] = $f5_stats['ltmVsStatusEntryState']['1.3.6.1.4.1.3375.2.2.10.13.2.1.2.' . $UID];
            if (($array['state'] == 2) || ($array['state'] == 3)) {
                // The Virtual Server is unavailable.
                $array['status'] = 2;
                $array['error'] = $f5_stats['ltmVsStatusEntryMsg']['1.3.6.1.4.1.3375.2.2.10.13.2.1.5.' . $UID];
            } else {
                // All is good.
                $array['status'] = 0;
                $array['error'] = '';
            }
        } elseif ($type == 'f5-ltm-poolmember') {
            $rrd_def = RrdDefinition::make()
                ->addDataset('currconns', 'GAUGE', 0);

            $array['state'] = $f5_stats['ltmPoolMbrStatusEntryState']['1.3.6.1.4.1.3375.2.2.5.6.2.1.5.' . $UID];
            $array['available'] = $f5_stats['ltmPoolMbrStatusEntryAvail']['1.3.6.1.4.1.3375.2.2.5.6.2.1.6.' . $UID];

            $fields = [
                'currconns' => $f5_stats['ltmPoolMemberStatEntryCurrconns']['1.3.6.1.4.1.3375.2.2.5.4.3.1.11.' . $UID],
            ];

            // Let's print some debugging info.
            d_echo("\n\nComponent: " . $key . "\n");
            d_echo('    Type: ' . $type . "\n");
            d_echo('    Label: ' . $label . "\n");

            // If available and bad state
            // 0 = None, 1 = Green, 2 = Yellow, 3 = Red, 4 = Blue
            if (($array['available'] == 1) && ($array['state'] == 3)) {
                // Warning Alarm, the pool member is down.
                $array['status'] = 1;
                $array['error'] = 'Pool Member is Down: ' . $f5_stats['ltmPoolMbrStatusEntryMsg']['1.3.6.1.4.1.3375.2.2.5.6.2.1.8.' . $UID];
            } else {
                // All is good.
                $array['status'] = 0;
                $array['error'] = '';
            }
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
