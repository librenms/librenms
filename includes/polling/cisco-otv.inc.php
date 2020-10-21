<?php
/*
 * LibreNMS module to capture Cisco Class-Based QoS Details
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

if ($device['os_group'] == 'cisco') {
    // Define some error messages
    $error_vpn = [];
    $error_vpn[0] = 'Other';
    $error_vpn[1] = 'Configuration changed';
    $error_vpn[2] = 'Control Group information is unavailable';
    $error_vpn[3] = 'Data Group range information is unavailable';
    $error_vpn[4] = 'Join or Source interface information is unavailable';
    $error_vpn[5] = 'VPN name is unavailable';
    $error_vpn[6] = 'IP address is missing for Join Interface';
    $error_vpn[7] = 'Join Interface is down';
    $error_vpn[8] = 'Overlay is administratively shutdown';
    $error_vpn[9] = 'Overlay is in delete hold down phase';
    $error_vpn[10] = 'VPN is reinitializing';
    $error_vpn[11] = 'Site ID information is unavailable';
    $error_vpn[12] = 'Site ID mismatch has occurred';
    $error_vpn[13] = 'IP address is missing for Source Interface';
    $error_vpn[14] = 'Source interface is down';
    $error_vpn[15] = 'Changing site identifier';
    $error_vpn[16] = 'Changing control group';
    $error_vpn[17] = 'Device ID information is unavailable';
    $error_vpn[18] = 'Changing device ID';
    $error_vpn[19] = 'Cleanup in progress';

    $error_aed = [];
    $error_aed[0] = 'Other';
    $error_aed[1] = 'Overlay is Down';
    $error_aed[2] = 'Site ID is not configured';
    $error_aed[3] = 'Site ID mismatch';
    $error_aed[4] = 'Version mismatch';
    $error_aed[5] = 'Site VLAN is Down';
    $error_aed[6] = 'No extended VLAN is operationally up';
    $error_aed[7] = 'No Overlay Adjacency is up';
    $error_aed[8] = 'LSPDB sync incomplete';
    $error_aed[9] = 'Overlay state down event in progress';
    $error_aed[10] = 'ISIS control group sync pending';

    $error_overlay = [];
    $error_overlay[1] = 'active';
    $error_overlay[2] = 'notInService';
    $error_overlay[3] = 'notReady';
    $error_overlay[4] = 'createAndGo';
    $error_overlay[5] = 'createAndWait';
    $error_overlay[6] = 'destroy';

    $tmp_module = 'Cisco-OTV';

    $component = new LibreNMS\Component();
    $options['filter']['type'] = ['=', $tmp_module];
    $options['filter']['disabled'] = ['=', 0];
    $components = $component->getComponents($device['device_id'], $options);

    // We only care about our device id.
    $components = $components[$device['device_id']];

    // Only collect SNMP data if we have enabled components
    if (is_array($components) && count($components) > 0) {
        // Let's gather the stats..
        $tblOverlayEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.2.1.1');
        $tblAdjacencyDatabaseEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.3.1.1', 0);
        $tblRouteNextHopAddr = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.5.1.1.8', 0);
        $tblVlanEdgeDevIsAed = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.2.2.1.6', 2);

        // Let's create an array of each remote OTV endpoint and the count of MAC addresses that are reachable via.
        $count_mac = [];
        foreach ($tblRouteNextHopAddr as $k => $v) {
            $count_mac[$v]++;
        }
        // Let's log some debugging
        d_echo("\n\nMAC Addresses: " . print_r($count_mac, true));

        // Loop through the components and extract the data.
        foreach ($components as $key => &$array) {
            if ($array['otvtype'] == 'overlay') {
                // Let's check the various status' of the overlay
                $message = false;
                $vpn_state = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.3'][$array['index']];
                if ($vpn_state != 2) {
                    $message .= 'VPN Down: ' . $error_vpn[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.4'][$array['index']]];
                }
                $aed_state = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.13'][$array['index']];
                if ($aed_state == 2) {
                    $message .= 'AED Down: ' . $error_aed[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.14'][$array['index']]];
                }
                $overlay_state = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.23'][$array['index']];
                if ($overlay_state == 2) {
                    $message .= 'Overlay Down: ' . $error_overlay[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.24'][$array['index']]];
                }

                // If we have set a message, we have an error, activate alert.
                if ($message !== false) {
                    $array['error'] = $message;
                    $array['status'] = 2;
                } else {
                    $array['error'] = '';
                    $array['status'] = 0;
                }

                // Time to graph the count of the active VLAN's on this overlay.
                $count_vlan = 0;
                foreach ($tblVlanEdgeDevIsAed['1.3.6.1.4.1.9.9.810.1.2.2.1.6'][$array['index']] as $v) {
                    if ($v == 1) {
                        $count_vlan++;
                    }
                }

                // Let's log some debugging
                d_echo("\n\nOverlay Component: " . $key . "\n");
                d_echo('    Label: ' . $array['label'] . "\n");
                d_echo('    Index: ' . $array['index'] . "\n");
                d_echo('    Status: ' . $array['status'] . "\n");
                d_echo('    Message: ' . $array['error'] . "\n");
                d_echo('    VLAN Count: ' . $count_vlan . "\n");

                $label = $array['label'];
                $rrd_name = ['cisco', 'otv', $label, 'vlan'];
                $rrd_def = RrdDefinition::make()->addDataset('count', 'GAUGE', 0);

                $fields = [
                    'count' => $count_vlan,
                ];

                $tags = compact('label', 'rrd_name', 'rrd_def');
                data_update($device, 'cisco-otv-vlan', $tags, $fields);
            } elseif ($array['otvtype'] == 'adjacency') {
                $array['uptime'] = $tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.6.' . $array['index'] . '.1.4.' . $array['endpoint']];
                $message = false;
                if ($tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.5.' . $array['index'] . '.1.4.' . $array['endpoint']] != 1) {
                    $message .= "Adjacency is Down\n";
                }
                if ($tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.6.' . $array['index'] . '.1.4.' . $array['endpoint']] < $array['uptime']) {
                    $message .= "Adjacency has been reset\n";
                }

                // If we have set a message, we have an error, activate alert.
                if ($message !== false) {
                    $array['error'] = $message;
                    $array['status'] = 1;
                } else {
                    $array['error'] = '';
                    $array['status'] = 0;
                }

                // Let's log some debugging
                d_echo("\n\nAdjacency Component: " . $key . "\n");
                d_echo('    Label: ' . $array['label'] . "\n");
                d_echo('    Index: ' . $array['index'] . "\n");
                d_echo('    Status: ' . $array['status'] . "\n");
                d_echo('    Message: ' . $array['error'] . "\n");
            } elseif ($array['otvtype'] == 'endpoint') {
                $count = 0;
                $endpoint = $array['endpoint'];

                if (isset($count_mac[$endpoint])) {
                    $count = $count_mac[$endpoint];
                }

                // Let's log some debugging
                d_echo("\n\nEndpoint Component: " . $key . "\n");
                d_echo('    Label: ' . $array['label'] . "\n");
                d_echo('    MAC Count: ' . $count . "\n");

                $rrd_name = ['cisco', 'otv', $endpoint, 'mac'];
                $rrd_def = RrdDefinition::make()->addDataset('count', 'GAUGE', 0);
                $fields = [
                    'count' => $count,
                ];

                $tags = compact('endpoint', 'rrd_name', 'rrd_def');
                data_update($device, 'cisco-otv-mac', $tags, $fields);
            } // End If
        } // End foreach components

        // Write the Components back to the DB.
        $component->setComponentPrefs($device['device_id'], $components);
    } // end if count components

    // Clean-up after yourself!
    unset(
        $components,
        $component,
        $tmp_module,
        $error_vpn,
        $error_aed,
        $error_overlay
    );
}
