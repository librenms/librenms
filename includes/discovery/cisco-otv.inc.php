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
    $ERROR_VPN[0] = "Other";
    $ERROR_VPN[1] = "Configuration changed";
    $ERROR_VPN[2] = "Control Group information is unavailable";
    $ERROR_VPN[3] = "Data Group range information is unavailable";
    $ERROR_VPN[4] = "Join or Source interface information is unavailable";
    $ERROR_VPN[5] = "VPN name is unavailable";
    $ERROR_VPN[6] = "IP address is missing for Join Interface";
    $ERROR_VPN[7] = "Join Interface is down";
    $ERROR_VPN[8] = "Overlay is administratively shutdown";
    $ERROR_VPN[9] = "Overlay is in delete hold down phase";
    $ERROR_VPN[10] = "VPN is reinitializing";
    $ERROR_VPN[11] = "Site ID information is unavailable";
    $ERROR_VPN[12] = "Site ID mismatch has occurred";
    $ERROR_VPN[13] = "IP address is missing for Source Interface";
    $ERROR_VPN[14] = "Source interface is down";
    $ERROR_VPN[15] = "Changing site identifier";
    $ERROR_VPN[16] = "Changing control group";
    $ERROR_VPN[17] = "Device ID information is unavailable";
    $ERROR_VPN[18] = "Changing device ID";
    $ERROR_VPN[19] = "Cleanup in progress";

    $ERROR_AED[0] = "Other";
    $ERROR_AED[1] = "Overlay is Down";
    $ERROR_AED[2] = "Site ID is not configured";
    $ERROR_AED[3] = "Site ID mismatch";
    $ERROR_AED[4] = "Version mismatch";
    $ERROR_AED[5] = "Site VLAN is Down";
    $ERROR_AED[6] = "No extended VLAN is operationally up";
    $ERROR_AED[7] = "No Overlay Adjacency is up";
    $ERROR_AED[8] = "LSPDB sync incomplete";
    $ERROR_AED[9] = "Overlay state down event in progress";
    $ERROR_AED[10] = "ISIS control group sync pending";

    $ERROR_OVERLAY[1] = "active";
    $ERROR_OVERLAY[2] = "notInService";
    $ERROR_OVERLAY[3] = "notReady";
    $ERROR_OVERLAY[4] = "createAndGo";
    $ERROR_OVERLAY[5] = "createAndWait";
    $ERROR_OVERLAY[6] = "destroy";

    $MODULE = 'Cisco-OTV';
    echo $MODULE.': ';

    require_once 'includes/component.php';
    $COMPONENT = new component();
    $COMPONENTS = $COMPONENT->getComponents($device['device_id'],array('type'=>$MODULE));

    // We only care about our device id.
    $COMPONENTS = $COMPONENTS[$device['device_id']];

    // Begin our master array, all other values will be processed into this array.
    $tblOTV = array();
    $tblEndpoints = array();

    // Let's gather some data..
    $tblOverlayEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.2.1.1');
    $tblAdjacencyDatabaseEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.3.1.1', 0);
    $tblAdjacentDevName = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.3.1.1.4', 0);

    /*
     * False == no object found - this is not an error, there is no QOS configured
     * null  == timeout or something else that caused an error, there may be QOS configured but we couldn't get it.
     */
    if ( is_null($tblOverlayEntry) || is_null($tblAdjacencyDatabaseEntry) || is_null($tblAdjacentDevName) ) {
        // We have to error here or we will end up deleting all our components.
        echo "Error\n";
    }
    else {
        // No Error, lets process things.

        // Add each overlay to the array.
        foreach ($tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.2'] as $index => $name) {
            $RESULT = array();
            $message = false;
            $RESULT['index'] = $index;
            $RESULT['label'] = $name;
            if ($tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.15'][$index] == 1) {
                $RESULT['transport'] = 'Multicast';
            }
            else {
                $RESULT['transport'] = 'Unicast';
            }
            $RESULT['otvtype'] = 'overlay';
            $RESULT['UID'] = $RESULT['otvtype']."-".$RESULT['index'];
            $RESULT['vpn_state'] = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.3'][$index];
            if ($RESULT['vpn_state'] != 2) {
                $message .= "VPN Down: ".$ERROR_VPN[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.4'][$index]]."\n";
            }
            $RESULT['aed_state'] = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.13'][$index];
            if ($RESULT['aed_state'] == 2) {
                $message .= "AED Down: ".$ERROR_AED[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.14'][$index]]."\n";
            }
            $RESULT['overlay_state'] = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.23'][$index];
            if ($RESULT['overlay_state'] == 2) {
                $message .= "Overlay Down: ".$ERROR_OVERLAY[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.24'][$index]]."\n";
            }

            // If we have set a message, we have an error, activate alert.
            if ($message !== false) {
                $RESULT['error'] = $message;
                $RESULT['status'] = 0;
            }
            else {
                $RESULT['error'] = "";
                $RESULT['status'] = 1;
            }

            // Add the result to the parent array.
            $tblOTV[] = $RESULT;
        }

        // Add each adjacency to the array.
        foreach ($tblAdjacentDevName as $key => $value) {
            preg_match('/^1.3.6.1.4.1.9.9.810.1.3.1.1.4.(\d+).1.4.(\d+.\d+.\d+.\d+)$/', $key, $MATCHES);
            $RESULT = array();
            $RESULT['index'] = $MATCHES[1];
            $RESULT['endpoint'] = $MATCHES[2];
            $tblEndpoints[$value] = true;
            $RESULT['otvtype'] = 'adjacency';
            $RESULT['UID'] = $RESULT['otvtype']."-".$RESULT['index']."-".str_replace(' ', '', $tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.3.'.$RESULT['index'].'.1.4.'.$RESULT['endpoint']]);
            $RESULT['uptime'] = $tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.6.'.$RESULT['index'].'.1.4.'.$RESULT['endpoint']];
            $message = false;
            if ($tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.5.'.$RESULT['index'].'.1.4.'.$RESULT['endpoint']] != 1) {
                $message .= "Adjacency is Down\n";
            }

            // If we have set a message, we have an error, activate alert.
            if ($message !== false) {
                $RESULT['error'] = $message;
                $RESULT['status'] = 0;
            }
            else {
                $RESULT['error'] = "";
                $RESULT['status'] = 1;
            }

            // Set a default name, if for some unknown reason we cant find the parent VPN.
            $RESULT['label'] = "Unknown (".$RESULT['index'].") - ".$value;
            // We need to search the existing array to build the name
            foreach ($tblOTV as $ITEM) {
                if (($ITEM['otvtype'] == 'overlay') && ($ITEM['index'] == $RESULT['index'])) {
                    $RESULT['label'] = $ITEM['label']." - ".$value;
                }
            }

            // Add the result to the parent array.
            $tblOTV[] = $RESULT;
        }

        // We retain a list of all endpoints to tie the RRD to.
        foreach ($tblEndpoints as $K => $V) {
            $RESULT['label'] = "Endpoint: ".$K;
            $RESULT['otvtype'] = 'endpoint';
            $RESULT['endpoint'] = $K;
            $RESULT['UID'] = $RESULT['otvtype']."-".$K;

            // Add the result to the parent array.
            $tblOTV[] = $RESULT;
        }

        /*
         * Ok, we have our 2 array's (Components and SNMP) now we need
         * to compare and see what needs to be added/updated.
         *
         * Let's loop over the SNMP data to see if we need to ADD or UPDATE any components.
         */
        foreach ($tblOTV as $key => $array) {
            $COMPONENT_KEY = false;

            // Loop over our components to determine if the component exists, or we need to add it.
            foreach ($COMPONENTS as $COMPID => $CHILD) {
                if ($CHILD['UID'] === $array['UID']) {
                    $COMPONENT_KEY = $COMPID;
                }
            }

            if (!$COMPONENT_KEY) {
                // The component doesn't exist, we need to ADD it - ADD.
                $NEW_COMPONENT = $COMPONENT->createComponent($device['device_id'],$MODULE);
                $COMPONENT_KEY = key($NEW_COMPONENT);
                $COMPONENTS[$COMPONENT_KEY] = array_merge($NEW_COMPONENT[$COMPONENT_KEY], $array);
                echo "+";
            }
            else {
                // The component does exist, merge the details in - UPDATE.
                $COMPONENTS[$COMPONENT_KEY] = array_merge($COMPONENTS[$COMPONENT_KEY], $array);
                echo ".";
            }

        }

        /*
         * Loop over the Component data to see if we need to DELETE any components.
         */
        foreach ($COMPONENTS as $key => $array) {
            // Guilty until proven innocent
            $FOUND = false;

            foreach ($tblOTV as $k => $v) {
                if ($array['UID'] == $v['UID']) {
                    // Yay, we found it...
                    $FOUND = true;
                }
            }

            if ($FOUND === false) {
                // The component has not been found. we should delete it.
                echo "-";
                $COMPONENT->deleteComponent($key);
            }
        }

        // Write the Components back to the DB.
        $COMPONENT->setComponentPrefs($device['device_id'],$COMPONENTS);
        echo "\n";

    } // End if not error

}
