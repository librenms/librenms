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
$components = $component->getComponents($device['device_id']);

// We only care about our device id.
$components = $components[$device['device_id']];

// We extracted all the components for this device, now lets only get the LTM ones.
$keep = array();
$types = array($module, 'bigip', 'f5-ltm-vs', 'f5-ltm-pool', 'f5-ltm-poolmember');
foreach ($components as $k => $v) {
    foreach ($types as $type) {
        if ($v['type'] == $type) {
            $keep[$k] = $v;
        }
    }
}
$components = $keep;

// Begin our master array, all other values will be processed into this array.
$tblBigIP = array();

// Let's gather some data..
$ltmVirtualServEntry = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.10.1.2.1', 0);
$ltmVsStatusEntry = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.10.13.2.1', 0);
$ltmPoolEntry = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.1.2.1', 0);
$ltmPoolMemberEntry = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.3.2.1', 0);
$ltmPoolMbrStatusEntry = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.6.2.1', 0);

/*
 * False == no object found - this is not an error, OID doesn't exist.
 * null  == timeout or something else that caused an error, OID may exist but we couldn't get it.
 */
if (!is_null($ltmVirtualServEntry) || !is_null($ltmVsStatusEntry) || !is_null($ltmPoolEntry) || !is_null($ltmPoolMemberEntry) || !is_null($ltmPoolMbrStatusEntry)) {
    // No Nulls, lets go....
    d_echo("Objects Found:\n");

    // Process the Virtual Servers
    if (is_array($ltmVsStatusEntry)) {
        foreach ($ltmVsStatusEntry as $oid => $value) {
            $result = array();

            // Find all Virtual server names and UID's, then we can find everything else we need.
            if (strpos($oid, '1.3.6.1.4.1.3375.2.2.10.13.2.1.1.') !== false) {
                list($null, $index) = explode('1.3.6.1.4.1.3375.2.2.10.13.2.1.1.', $oid);
                $result['type'] = 'f5-ltm-vs';
                $result['UID'] = (string)$index;
                $result['label'] = $value;
                // The UID is far too long to have in a RRD filename, use a hash of it instead.
                $result['hash'] = hash('crc32', $result['UID']);

                // Now that we have our UID we can pull all the other data we need.
                $result['IP'] = hex_to_ip($ltmVirtualServEntry['1.3.6.1.4.1.3375.2.2.10.1.2.1.3.'.$index]);
                $result['port'] = $ltmVirtualServEntry['1.3.6.1.4.1.3375.2.2.10.1.2.1.6.'.$index];
                $result['pool'] = $ltmVirtualServEntry['1.3.6.1.4.1.3375.2.2.10.1.2.1.19.'.$index];

                // 0 = None, 1 = Green, 2 = Yellow, 3 = Red, 4 = Blue
                $result['state'] = $ltmVsStatusEntry['1.3.6.1.4.1.3375.2.2.10.13.2.1.2.'.$index];
                if ($result['state'] == 2) {
                    // Looks like one of the VS Pool members is down.
                    $result['status'] = 1;
                    $result['error'] = $ltmVsStatusEntry['1.3.6.1.4.1.3375.2.2.10.13.2.1.5.'.$index];
                } elseif ($result['state'] == 3) {
                    // Looks like ALL of the VS Pool members is down.
                    $result['status'] = 2;
                    $result['error'] = $ltmVsStatusEntry['1.3.6.1.4.1.3375.2.2.10.13.2.1.5.'.$index];
                } else {
                    // All is good.
                    $result['status'] = 0;
                    $result['error'] = '';
                }
            }

            // Do we have any results
            if (count($result) > 0) {
                // Let's log some debugging
                d_echo("\n\n".$result['type'].": ".$result['label']."\n");
                d_echo("    Status:  ".$result['status']."\n");
                d_echo("    Message: ".$result['error']."\n");

                // Add this result to the master array.
                $tblBigIP[] = $result;
            }
        }
    }

    // Process the Pools
    if (is_array($ltmPoolEntry)) {
        foreach ($ltmPoolEntry as $oid => $value) {
            $result = array ();

            // Find all Pool names and UID's, then we can find everything else we need.
            if (strpos($oid, '1.3.6.1.4.1.3375.2.2.5.1.2.1.1.') !== false) {
                list($null, $index) = explode('1.3.6.1.4.1.3375.2.2.5.1.2.1.1.', $oid);
                $result['type'] = 'f5-ltm-pool';
                $result['UID'] = (string)$index;
                $result['label'] = $value;
                // The UID is far too long to have in a RRD filename, use a hash of it instead.
                $result['hash'] = hash('crc32', $result['UID']);

                // Now that we have our UID we can pull all the other data we need.
                $result['mode'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.2.'.$index];
                $result['minup'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.4.'.$index];
                $result['minupstatus'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.5.'.$index];
                $result['currentup'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.8.'.$index];
                $result['minupaction'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.6.'.$index];
                $result['monitor'] = $ltmPoolEntry['1.3.6.1.4.1.3375.2.2.5.1.2.1.17.'.$index];

                // If we have less pool members than the minimum, we should error.
                if ($result['currentup'] < $result['minup']) {
                    // Danger Will Robinson... We dont have enough Pool Members!
                    $result['status'] = 2;
                    $result['error'] = "Minimum Pool Members not met. Action taken: ".$error_poolaction[$result['minupaction']];
                } else {
                    // All is good.
                    $result['status'] = 0;
                    $result['error'] = '';
                }
            }

            // Do we have any results
            if (count($result) > 0) {
                // Let's log some debugging
                d_echo("\n\n".$result['type'].": ".$result['label']."\n");
                d_echo("    Status:            ".$result['status']."\n");
                d_echo("    Message:           ".$result['error']."\n");

                // Add this result to the master array.
                $tblBigIP[] = $result;
            }
        }
    }

    // Process the Pool Members
    if (is_array($ltmPoolMemberEntry)) {
        foreach ($ltmPoolMemberEntry as $oid => $value) {
            $result = array ();

            // Find all Pool member names and UID's, then we can find everything else we need.
            if (strpos($oid, '1.3.6.1.4.1.3375.2.2.5.3.2.1.19.') !== false) {
                list($null, $index) = explode('1.3.6.1.4.1.3375.2.2.5.3.2.1.19.', $oid);
                $result['type'] = 'f5-ltm-poolmember';
                $result['UID'] = (string)$index;
                $result['label'] = $value;
                // The UID is far too long to have in a RRD filename, use a hash of it instead.
                $result['hash'] = hash('crc32', $result['UID']);

                // Now that we have our UID we can pull all the other data we need.
                $result['IP'] = hex_to_ip($ltmPoolMemberEntry['1.3.6.1.4.1.3375.2.2.5.3.2.1.3.'.$index]);
                $result['port'] = $ltmPoolMemberEntry['1.3.6.1.4.1.3375.2.2.5.3.2.1.4.'.$index];
                $result['ratio'] = $ltmPoolMemberEntry['1.3.6.1.4.1.3375.2.2.5.3.2.1.6.'.$index];
                $result['weight'] = $ltmPoolMemberEntry['1.3.6.1.4.1.3375.2.2.5.3.2.1.7.'.$index];
                $result['priority'] = $ltmPoolMemberEntry['1.3.6.1.4.1.3375.2.2.5.3.2.1.8.'.$index];
                $result['state'] = $ltmPoolMbrStatusEntry['1.3.6.1.4.1.3375.2.2.5.6.2.1.5.'.$index];
                $result['available'] = $ltmPoolMbrStatusEntry['1.3.6.1.4.1.3375.2.2.5.6.2.1.6.'.$index];

                // If available and bad state
                // 0 = None, 1 = Green, 2 = Yellow, 3 = Red, 4 = Blue
                if (($result['available'] == 1) && ($result['state'] == 3)) {
                    // Warning Alarm, the pool member is down.
                    $result['status'] = 1;
                    $result['error'] = "Pool Member is Down: ".$ltmPoolMbrStatusEntry['1.3.6.1.4.1.3375.2.2.5.6.2.1.8.'.$index];
                } else {
                    // All is good.
                    $result['status'] = 0;
                    $result['error'] = '';
                }
            }

            // Do we have any results
            if (count($result) > 0) {
                // Let's log some debugging
                d_echo("\n\n".$result['type'].": ".$result['label']."\n");
                d_echo("    Status:   ".$result['status']."\n");
                d_echo("    Message:  ".$result['error']."\n");

                // Add this result to the master array.
                $tblBigIP[] = $result;
            }
        }
    }

    /*
     * Ok, we have our 2 array's (Components and SNMP) now we need
     * to compare and see what needs to be added/updated.
     *
     * Let's loop over the SNMP data to see if we need to ADD or UPDATE any components.
     */
    foreach ($tblBigIP as $key => $array) {
        $component_key = false;

        // Loop over our components to determine if the component exists, or we need to add it.
        foreach ($components as $compid => $child) {
            if (($child['UID'] === $array['UID']) && ($child['type'] === $array['type'])) {
                $component_key = $compid;
            }
        }

        if (!$component_key) {
            // The component doesn't exist, we need to ADD it - ADD.
            $new_component = $component->createComponent($device['device_id'], $array['type']);
            $component_key = key($new_component);
            $components[$component_key] = array_merge($new_component[$component_key], $array);
            echo "+";
        } else {
            // The component does exist, merge the details in - UPDATE.
            $components[$component_key] = array_merge($components[$component_key], $array);
            echo ".";
        }
    }

    /*
     * Loop over the Component data to see if we need to DELETE any components.
     */
    foreach ($components as $key => $array) {
        // Guilty until proven innocent
        $found = false;

        foreach ($tblBigIP as $k => $v) {
            if (($array['UID'] == $v['UID']) && ($array['type'] == $v['type'])) {
                // Yay, we found it...
                $found = true;
            }
        }

        if ($found === false) {
            // The component has not been found. we should delete it.
            echo "-";
            $component->deleteComponent($key);
        }
    }

    // Write the Components back to the DB.
    $component->setComponentPrefs($device['device_id'], $components);
    echo "\n";
} // End if not error
