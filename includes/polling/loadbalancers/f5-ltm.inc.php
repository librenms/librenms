<?php
/*
 * LibreNMS module to display F5 LTM Details
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// Define some error messages
$error_poolaction = array();
$error_poolaction[0] = "Unused";
$error_poolaction[1] = "Reboot";
$error_poolaction[2] = "Restart";
$error_poolaction[3] = "Failover";
$error_poolaction[4] = "Failover and Restart";
$error_poolaction[5] = "Go Active";
$error_poolaction[6] = "None";

$component = new LibreNMS\Component();
$options['filter']['disabled'] = array('=',0);
$options['filter']['ignore'] = array('=',0);
$components = $component->getComponents($device['device_id'], $options);

// We only care about our device id.
$components = $components[$device['device_id']];

// We extracted all the components for this device, now lets only get the LTM ones.
$keep = array();
$types = array('f5-ltm-vs', 'f5-ltm-pool', 'f5-ltm-poolmember');
foreach ($components as $k => $v) {
    foreach ($types as $type) {
        if ($v['type'] == $type) {
            $keep[$k] = $v;
        }
    }
}
$components = $keep;

// Only collect SNMP data if we have enabled components
if (count($components > 0)) {
    // Let's gather the stats..
    $ltmVirtualServStatEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.3375.2.2.10.2.3.1', 0);
    $ltmPoolMemberStatEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.3375.2.2.5.4.3.1', 0);
    $sysGlobalHttpStat = snmpwalk_array_num($device, '.1.3.6.1.4.1.3375.2.1.1.2.4', 0);

    // and check the status
    $ltmVsStatusEntry = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.10.13.2.1', 0);
    $ltmPoolMbrStatusEntry = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.6.2.1', 0);
    $ltmPoolEntry = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.1.2.1', 0);

    // Loop through the components and extract the data.
    foreach ($components as $key => &$array) {
        $type = $array['type'];
        $UID = $array['UID'];
        $label = $array['label'];
        $hash = $array['hash'];
        $rrd_name = array($type, $label, $hash);

        if ($type == 'f5-ltm-vs') {
            $rrd_def = array(
                'DS:pktsin:COUNTER:600:0:U',
                'DS:pktsout:COUNTER:600:0:U',
                'DS:bytesin:COUNTER:600:0:U',
                'DS:bytesout:COUNTER:600:0:U',
                'DS:totconns:COUNTER:600:0:U',
            );

            $fields = array(
                'pktsin' => $ltmVirtualServStatEntry['1.3.6.1.4.1.3375.2.2.10.2.3.1.6.'.$UID],
                'pktsout' => $ltmVirtualServStatEntry['1.3.6.1.4.1.3375.2.2.10.2.3.1.8.'.$UID],
                'bytesin' => $ltmVirtualServStatEntry['1.3.6.1.4.1.3375.2.2.10.2.3.1.7.'.$UID],
                'bytesout' => $ltmVirtualServStatEntry['1.3.6.1.4.1.3375.2.2.10.2.3.1.9.'.$UID],
                'totconns' => $ltmVirtualServStatEntry['1.3.6.1.4.1.3375.2.2.10.2.3.1.11.'.$UID],
            );

            // Let's print some debugging info.
            d_echo("\n\nComponent: ".$key."\n");
            d_echo("    Type: ".$type."\n");
            d_echo("    Label: ".$label."\n");

            // Let's check the status.
            $array['state'] = $ltmVsStatusEntry['1.3.6.1.4.1.3375.2.2.10.13.2.1.2.'.$UID];
            if (($array['state'] == 2) || ($array['state'] == 3)) {
                // The Virtual Server is unavailable.
                $array['status'] = 2;
                $array['error'] = $ltmVsStatusEntry['1.3.6.1.4.1.3375.2.2.10.13.2.1.5.'.$UID];
            } else {
                // All is good.
                $array['status'] = 0;
                $array['error'] = '';
            }
        } elseif ($type == 'f5-ltm-pool') {
            $rrd_def = array(
                'DS:minup:GAUGE:600:0:U',
                'DS:currup:GAUGE:600:0:U',
            );

            $array['minup'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.4.'.$UID];
            $array['minupstatus'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.5.'.$index];
            $array['currentup'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.8.'.$UID];
            $array['minupaction'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.6.'.$UID];

            $fields = array(
                'minup' => $array['minup'],
                'currup' => $array['currentup'],
            );

            // Let's print some debugging info.
            d_echo("\n\nComponent: ".$key."\n");
            d_echo("    Type: ".$type."\n");
            d_echo("    Label: ".$label."\n");

            // If we have less pool members than the minimum, we should error.
            if ($array['currentup'] < $array['minup']) {
                // Danger Will Robinson... We dont have enough Pool Members!
                $array['status'] = 2;
                $array['error'] = "Minimum Pool Members not met. Action taken: ".$error_poolaction[$array['minupaction']];
            } else {
                // All is good.
                $array['status'] = 0;
                $array['error'] = '';
            }
        } elseif ($type == 'f5-ltm-poolmember') {
            $rrd_def = array(
                'DS:pktsin:COUNTER:600:0:U',
                'DS:pktsout:COUNTER:600:0:U',
                'DS:bytesin:COUNTER:600:0:U',
                'DS:bytesout:COUNTER:600:0:U',
                'DS:totconns:COUNTER:600:0:U',
            );

            $array['state'] = $ltmPoolMbrStatusEntry['1.3.6.1.4.1.3375.2.2.5.6.2.1.5.'.$UID];
            $array['available'] = $ltmPoolMbrStatusEntry['1.3.6.1.4.1.3375.2.2.5.6.2.1.6.'.$UID];

            $fields = array(
                'pktsin' => $ltmPoolMemberStatEntry['1.3.6.1.4.1.3375.2.2.5.4.3.1.5.'.$UID],
                'pktsout' => $ltmPoolMemberStatEntry['1.3.6.1.4.1.3375.2.2.5.4.3.1.7.'.$UID],
                'bytesin' => $ltmPoolMemberStatEntry['1.3.6.1.4.1.3375.2.2.5.4.3.1.6.'.$UID],
                'bytesout' => $ltmPoolMemberStatEntry['1.3.6.1.4.1.3375.2.2.5.4.3.1.8.'.$UID],
                'totalconns' => $ltmPoolMemberStatEntry['1.3.6.1.4.1.3375.2.2.5.4.3.1.10.'.$UID],
            );

            // Let's print some debugging info.
            d_echo("\n\nComponent: ".$key."\n");
            d_echo("    Type: ".$type."\n");
            d_echo("    Label: ".$label."\n");

            // If available and bad state
            // 0 = None, 1 = Green, 2 = Yellow, 3 = Red, 4 = Blue
            if (($array['available'] == 1) && ($array['state'] == 3)) {
                // Warning Alarm, the pool member is down.
                $array['status'] = 1;
                $array['error'] = "Pool Member is Down: ".$ltmPoolMbrStatusEntry['1.3.6.1.4.1.3375.2.2.5.6.2.1.8.'.$UID];
            } else {
                // All is good.
                $array['status'] = 0;
                $array['error'] = '';
            }
        } else {
            d_echo("Type is unknown: ".$type."\n");
            continue;
        }

        $tags = compact('rrd_name', 'rrd_def', 'type', 'hash', 'label');
        data_update($device, $type, $tags, $fields);
    } // End foreach components

    // Write the Components back to the DB.
    $component->setComponentPrefs($device['device_id'], $components);
} // end if count components

// Clean-up after yourself!
unset($type, $components, $component, $options);
