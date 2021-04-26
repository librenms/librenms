<?php
/*
 * LibreNMS module to capture Cisco OTV Details
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

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

    $module = 'Cisco-OTV';

    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id'], ['type'=>$module]);

    // We only care about our device id.
    $components = $components[$device['device_id']];

    // Begin our master array, all other values will be processed into this array.
    $tblOTV = [];
    $tblEndpoints = [];

    // Let's gather some data..
    $tblOverlayEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.2.1.1');
    $tblAdjacencyDatabaseEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.3.1.1', 0);
    $tblAdjacentDevName = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.3.1.1.4', 0);

    /*
     * False == no object found - this is not an error, there is no QOS configured
     * null  == timeout or something else that caused an error, there may be QOS configured but we couldn't get it.
     */
    if (is_null($tblOverlayEntry) || is_null($tblAdjacencyDatabaseEntry) || is_null($tblAdjacentDevName)) {
        // We have to error here or we will end up deleting all our components.
        echo "Error\n";
    } else {
        // No Error, lets process things.

        // Add each overlay to the array.
        foreach ((array) $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.2'] as $index => $name) {
            $result = [];
            $message = false;
            $result['index'] = $index;
            $result['label'] = $name;
            if ($tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.15'][$index] == 1) {
                $result['transport'] = 'Multicast';
            } else {
                $result['transport'] = 'Unicast';
            }
            $result['otvtype'] = 'overlay';
            $result['UID'] = $result['otvtype'] . '-' . $result['index'];
            $result['vpn_state'] = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.3'][$index];
            if ($result['vpn_state'] != 2) {
                $message .= 'VPN Down: ' . $error_vpn[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.4'][$index]] . "\n";
            }
            $result['aed_state'] = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.13'][$index];
            if ($result['aed_state'] == 2) {
                $message .= 'AED Down: ' . $error_aed[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.14'][$index]] . "\n";
            }
            $result['overlay_state'] = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.23'][$index];
            if ($result['overlay_state'] == 2) {
                $message .= 'Overlay Down: ' . $error_overlay[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.24'][$index]] . "\n";
            }

            // If we have set a message, we have an error, activate alert.
            if ($message !== false) {
                $result['error'] = $message;
                $result['status'] = 2;
            } else {
                $result['error'] = '';
                $result['status'] = 0;
            }

            // Let's log some debugging
            d_echo("\n\nOverlay: " . $result['label'] . "\n");
            d_echo('    Index: ' . $result['index'] . "\n");
            d_echo('    UID: ' . $result['UID'] . "\n");
            d_echo('    Transport: ' . $result['transport'] . "\n");
            d_echo('    Type: ' . $result['otvtype'] . "\n");
            d_echo('    Status: ' . $result['status'] . "\n");
            d_echo('    Message: ' . $result['error'] . "\n");

            // Add the result to the parent array.
            $tblOTV[] = $result;
        }

        // Add each adjacency to the array.
        if ($tblAdjacentDevName) {
            foreach ((array) $tblAdjacentDevName as $key => $value) {
                preg_match('/^1.3.6.1.4.1.9.9.810.1.3.1.1.4.(\d+).1.4.(\d+.\d+.\d+.\d+)$/', $key, $matches);
                $result = [];
                $result['index'] = $matches[1];
                $result['endpoint'] = $matches[2];
                $tblEndpoints[$value] = true;
                $result['otvtype'] = 'adjacency';
                $result['UID'] = $result['otvtype'] . '-' . $result['index'] . '-' . str_replace(' ', '', $tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.3.' . $result['index'] . '.1.4.' . $result['endpoint']]);
                $result['uptime'] = $tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.6.' . $result['index'] . '.1.4.' . $result['endpoint']];
                $message = false;
                if ($tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.5.' . $result['index'] . '.1.4.' . $result['endpoint']] != 1) {
                    $message .= "Adjacency is Down\n";
                }

                // If we have set a message, we have an error, activate alert.
                if ($message !== false) {
                    $result['error'] = $message;
                    $result['status'] = 1;
                } else {
                    $result['error'] = '';
                    $result['status'] = 0;
                }

                // Set a default name, if for some unknown reason we cant find the parent VPN.
                $result['label'] = 'Unknown (' . $result['index'] . ') - ' . $value;
                // We need to search the existing array to build the name
                foreach ($tblOTV as $item) {
                    if (($item['otvtype'] == 'overlay') && ($item['index'] == $result['index'])) {
                        $result['label'] = $item['label'] . ' - ' . $value;
                    }
                }

                // Let's log some debugging
                d_echo("\n\nAdjacency: " . $result['label'] . "\n");
                d_echo('    Endpoint: ' . $result['endpoint'] . "\n");
                d_echo('    Index: ' . $result['index'] . "\n");
                d_echo('    UID: ' . $result['UID'] . "\n");
                d_echo('    Status: ' . $result['status'] . "\n");
                d_echo('    Message: ' . $result['error'] . "\n");

                // Add the result to the parent array.
                $tblOTV[] = $result;
            }
        }

        // We retain a list of all endpoints to tie the RRD to.
        foreach ($tblEndpoints as $k => $v) {
            $result['label'] = 'Endpoint: ' . $k;
            $result['otvtype'] = 'endpoint';
            $result['endpoint'] = $k;
            $result['UID'] = $result['otvtype'] . '-' . $k;

            // Let's log some debugging
            d_echo("\n\nEndpoint: " . $result['label'] . "\n");
            d_echo('    UID: ' . $result['UID'] . "\n");
            d_echo('    Type: ' . $result['otvtype'] . "\n");

            // Add the result to the parent array.
            $tblOTV[] = $result;
        }

        /*
         * Ok, we have our 2 array's (Components and SNMP) now we need
         * to compare and see what needs to be added/updated.
         *
         * Let's loop over the SNMP data to see if we need to ADD or UPDATE any components.
         */
        foreach ($tblOTV as $key => $array) {
            $component_key = false;

            // Loop over our components to determine if the component exists, or we need to add it.
            foreach ((array) $components as $compid => $child) {
                if ($child['UID'] === $array['UID']) {
                    $component_key = $compid;
                }
            }

            if (! $component_key) {
                // The component doesn't exist, we need to ADD it - ADD.
                $new_component = $component->createComponent($device['device_id'], $module);
                $component_key = key($new_component);
                $components[$component_key] = array_merge($new_component[$component_key], $array);
                echo '+';
            } else {
                // The component does exist, merge the details in - UPDATE.
                $components[$component_key] = array_merge($components[$component_key], $array);
                echo '.';
            }
        }

        /*
         * Loop over the Component data to see if we need to DELETE any components.
         */
        foreach ((array) $components as $key => $array) {
            // Guilty until proven innocent
            $found = false;

            foreach ($tblOTV as $k => $v) {
                if ($array['UID'] == $v['UID']) {
                    // Yay, we found it...
                    $found = true;
                }
            }

            if ($found === false) {
                // The component has not been found. we should delete it.
                echo '-';
                $component->deleteComponent($key);
            }
        }

        // Write the Components back to the DB.
        $component->setComponentPrefs($device['device_id'], $components);
        echo "\n";
    } // End if not error
}
