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

if ($device['os_group'] == "cisco") {

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

    require_once 'includes/component.php';
    $COMPONENT = new component();
    $options['filter']['type'] = array('=',$MODULE);
    $options['filter']['disabled'] = array('=',0);
    $COMPONENTS = $COMPONENT->getComponents($device['device_id'],$options);

    // We only care about our device id.
    $COMPONENTS = $COMPONENTS[$device['device_id']];

    // Only collect SNMP data if we have enabled components
    if (count($COMPONENTS > 0)) {
        // Let's gather the stats..
        $tblOverlayEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.2.1.1');
        $tblAdjacencyDatabaseEntry = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.3.1.1', 0);
        $tblRouteNextHopAddr = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.5.1.1.8', 0);
        $tblVlanEdgeDevIsAed = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.810.1.2.2.1.6', 2);

        // Let's create an array of each remote OTV endpoint and the count of MAC addresses that are reachable via.
        $COUNT_MAC = array();
        foreach ($tblRouteNextHopAddr as $k => $v) {
            $COUNT_MAC[$v]++;
        }

        // Loop through the components and extract the data.
        foreach ($COMPONENTS as $KEY => &$ARRAY) {

            if ($ARRAY['otvtype'] == 'overlay') {
                // Let's check the varius status' of the overlay
                $message = false;
                $vpn_state = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.3'][$ARRAY['index']];
                if ($vpn_state != 2) {
                    $message .= "VPN Down: ".$ERROR_VPN[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.4'][$ARRAY['index']]];
                }
                $aed_state = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.13'][$ARRAY['index']];
                if ($aed_state == 2) {
                    $message .= "AED Down: ".$ERROR_AED[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.14'][$ARRAY['index']]];
                }
                $overlay_state = $tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.23'][$ARRAY['index']];
                if ($overlay_state == 2) {
                    $message .= "Overlay Down: ".$ERROR_OVERLAY[$tblOverlayEntry['1.3.6.1.4.1.9.9.810.1.2.1.1.24'][$ARRAY['index']]];
                }

                // If we have set a message, we have an error, activate alert.
                if ($message !== false) {
                    $ARRAY['error'] = $message;
                    $ARRAY['status'] = 0;
                }
                else {
                    $ARRAY['error'] = "";
                    $ARRAY['status'] = 1;
                }

                // Time to graph the count of the active VLAN's on this overlay.
                $COUNT_VLAN = 0;
                foreach ($tblVlanEdgeDevIsAed['1.3.6.1.4.1.9.9.810.1.2.2.1.6'][$ARRAY['index']] as $v) {
                    if ($v == 1) {
                        $COUNT_VLAN++;
                    }
                }

                $filename = "cisco-otv-".$ARRAY['label']."-vlan.rrd";
                $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ($filename);

                if (!file_exists ($rrd_filename)) {
                    rrdtool_create ($rrd_filename, " DS:count:GAUGE:600:0:U" . $config['rrd_rra']);
                }
                $RRD['count'] = $COUNT_VLAN;

                // Update RRD
                rrdtool_update ($rrd_filename, $RRD);

            }
            elseif ($ARRAY['otvtype'] == 'adjacency') {
                $ARRAY['uptime'] = $tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.6.'.$ARRAY['index'].'.1.4.'.$ARRAY['endpoint']];
                $message = false;
                if ($tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.5.'.$ARRAY['index'].'.1.4.'.$ARRAY['endpoint']] != 1) {
                    $message .= "Adjacency is Down\n";
                }
                if ($tblAdjacencyDatabaseEntry['1.3.6.1.4.1.9.9.810.1.3.1.1.6.'.$ARRAY['index'].'.1.4.'.$ARRAY['endpoint']] < $ARRAY['uptime']) {
                    $message .= "Adjacency has been reset\n";
                }

                // If we have set a message, we have an error, activate alert.
                if ($message !== false) {
                    $ARRAY['error'] = $message;
                    $ARRAY['status'] = 0;
                }
                else {
                    $ARRAY['error'] = "";
                    $ARRAY['status'] = 1;
                }
            }
            elseif ($ARRAY['otvtype'] == 'endpoint') {
                if (isset($COUNT_MAC[$ARRAY['endpoint']])) {
                    $RRD['count'] = $COUNT_MAC[$ARRAY['endpoint']];
                }
                else {
                    $RRD['count'] = "0";
                }

                $filename = "cisco-otv-".$ARRAY['endpoint']."-mac.rrd";
                $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ($filename);

                if (!file_exists ($rrd_filename)) {
                    rrdtool_create ($rrd_filename, " DS:count:GAUGE:600:0:U" . $config['rrd_rra']);
                }

                // Update RRD
                rrdtool_update ($rrd_filename, $RRD);

            } // End If

        } // End foreach components

        // Write the Components back to the DB.
        $COMPONENT->setComponentPrefs($device['device_id'],$COMPONENTS);

        echo $MODULE." ";
    } // end if count components

    // Clean-up after yourself!
    unset($COMPONENTS, $COMPONENT, $MODULE);
}