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
use LibreNMS\Util\IP;

$error_poolaction = [];
$error_poolaction[0] = 'Unused';
$error_poolaction[1] = 'Reboot';
$error_poolaction[2] = 'Restart';
$error_poolaction[3] = 'Failover';
$error_poolaction[4] = 'Failover and Restart';
$error_poolaction[5] = 'Go Active';
$error_poolaction[6] = 'None';

$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id']);

// We only care about our device id.
$components = $components[$device['device_id']];

// We extracted all the components for this device, now lets only get the LTM ones.
$keep = [];
$types = [$module, 'bigip', 'f5-ltm-bwc', 'f5-ltm-vs', 'f5-ltm-pool', 'f5-ltm-poolmember'];
foreach ($components as $k => $v) {
    foreach ($types as $type) {
        if ($v['type'] == $type) {
            $keep[$k] = $v;
        }
    }
}
$components = $keep;

// Begin our master array, all other values will be processed into this array.
$tblBigIP = [];

// Virtual Server Data
$ltmVirtualServOID = [
    'ip' => '1.3.6.1.4.1.3375.2.2.10.1.2.1.3',
    'port' => '1.3.6.1.4.1.3375.2.2.10.1.2.1.6',
    'defaultpool' => '1.3.6.1.4.1.3375.2.2.10.1.2.1.19',
    'state' => '1.3.6.1.4.1.3375.2.2.10.13.2.1.2',
    'errorcode' => '1.3.6.1.4.1.3375.2.2.10.13.2.1.5',
];

$ltmVirtualServEntry = [];
//Check for Virtual Server Enries
$ltmVirtualServEntry['name'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.10.1.2.1.1', 0);
//If no Virtual Servers are found don't look for statistics
if (! empty($ltmVirtualServEntry['name'])) {
    foreach ($ltmVirtualServOID as $key => $value) {
        $ltmVirtualServEntry[$key] = snmpwalk_array_num($device, $value, 0);
    }
} else {
    d_echo("No Virtual Servers Found\n");
}

// Pool Data
$ltmPoolEntryOID = [
    'mode' => '1.3.6.1.4.1.3375.2.2.5.1.2.1.2',
    'minup' => '1.3.6.1.4.1.3375.2.2.5.1.2.1.4',
    'minupstatus' => '1.3.6.1.4.1.3375.2.2.5.1.2.1.5',
    'minupaction' => '1.3.6.1.4.1.3375.2.2.5.1.2.1.6',
    'currentup' => '1.3.6.1.4.1.3375.2.2.5.1.2.1.8',
    'monitor' => '1.3.6.1.4.1.3375.2.2.5.1.2.1.17',
];

// Pool Member Data
$ltmPoolMemberEntryOID = [
    'ip' => '1.3.6.1.4.1.3375.2.2.5.3.2.1.3',
    'port' => '1.3.6.1.4.1.3375.2.2.5.3.2.1.4',
    'ratio' => '1.3.6.1.4.1.3375.2.2.5.3.2.1.6',
    'weight' => '1.3.6.1.4.1.3375.2.2.5.3.2.1.7',
    'priority' => '1.3.6.1.4.1.3375.2.2.5.3.2.1.8',
    'nodename' => '1.3.6.1.4.1.3375.2.2.5.3.2.1.19',
    'state' => '1.3.6.1.4.1.3375.2.2.5.6.2.1.5',
    'available' => '1.3.6.1.4.1.3375.2.2.5.6.2.1.6',
    'errorcode' => '1.3.6.1.4.1.3375.2.2.5.6.2.1.8',
];

//Check for Pool Enries
$ltmPoolEntry = [];
$ltmPoolMemberEntry = [];
$ltmPoolEntry['name'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.1.2.1.1', 0);

//If no Pools are found don't look for statistics or pool members
if (! empty($ltmPoolEntry['name'])) {
    // If there are pools gather Pool Member Data
    foreach ($ltmPoolEntryOID as $key => $value) {
        $ltmPoolEntry[$key] = snmpwalk_array_num($device, $value, 0);
    }
    // Gather Pool Member Data if pool members found
    $ltmPoolMemberEntry['name'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.5.3.2.1.1', 0);
    if (! empty($ltmPoolMemberEntry['name'])) {
        foreach ($ltmPoolMemberEntryOID as $key => $value) {
            $ltmPoolMemberEntry[$key] = snmpwalk_array_num($device, $value, 0);
        }
    }
} else {
    d_echo("No Pools Found\n");
}

echo "#### Unload disco module ltm-bwc ####\n\n";
$ltmBwcEntry = [];
//Check for Virtual Server Enries
$ltmBwcEntry['name'] = snmpwalk_array_num($device, '1.3.6.1.4.1.3375.2.2.13.1.3.1.1', 0);
//If no BWC are found don't look for statistics
if (! empty($ltmBwcEntry['name'])) {
    d_echo("#### Bandwidth Controller Found\n");
} else {
    d_echo("No Bandwidth Controller Found\n");
}

/*
 * False == no object found - this is not an error, OID doesn't exist.
 * null  == timeout or something else that caused an error, OID may exist but we couldn't get it.
 */
if (! empty($ltmBwcEntry) || ! empty($ltmVirtualServEntry) || ! empty($ltmPoolEntry) || ! empty($ltmPoolMemberEntry)) {
    // No Nulls, lets go....
    d_echo("Objects Found:\n");

    // Process the Virtual Servers
    if (is_array($ltmVirtualServEntry['name'])) {
        foreach ($ltmVirtualServEntry['name'] as $oid => $value) {
            $result = [];

            // Find all Virtual server names and UID's, then we can find everything else we need.
            if (strpos($oid, '1.3.6.1.4.1.3375.2.2.10.1.2.1.1.') !== false) {
                [$null, $index] = explode('1.3.6.1.4.1.3375.2.2.10.1.2.1.1.', $oid);
                $result['type'] = 'f5-ltm-vs';
                $result['UID'] = (string) $index;
                $result['label'] = $value;
                // The UID is far too long to have in a RRD filename, use a hash of it instead.
                $result['hash'] = hash('crc32', $result['UID']);

                // Trim IPv4 response to remove route domain ID
                if (strlen($ltmVirtualServEntry['ip']['1.3.6.1.4.1.3375.2.2.10.1.2.1.3.' . $index]) == 23) {
                    $ltmVirtualServEntry['ip']['1.3.6.1.4.1.3375.2.2.10.1.2.1.3.' . $index] = substr($ltmVirtualServEntry['ip']['1.3.6.1.4.1.3375.2.2.10.1.2.1.3.' . $index], 0, 11);
                }

                // Now that we have our UID we can pull all the other data we need.
                $result['IP'] = IP::fromHexString($ltmVirtualServEntry['ip']['1.3.6.1.4.1.3375.2.2.10.1.2.1.3.' . $index], true);
                $result['port'] = $ltmVirtualServEntry['port']['1.3.6.1.4.1.3375.2.2.10.1.2.1.6.' . $index];
                $result['pool'] = $ltmVirtualServEntry['defaultpool']['1.3.6.1.4.1.3375.2.2.10.1.2.1.19.' . $index];

                // 0 = None, 1 = Green, 2 = Yellow, 3 = Red, 4 = Blue
                $result['state'] = $ltmVirtualServEntry['state']['1.3.6.1.4.1.3375.2.2.10.13.2.1.2.' . $index];
                if ($result['state'] == 2) {
                    // Looks like one of the VS Pool members is down.
                    $result['status'] = 1;
                    $result['error'] = $ltmVirtualServEntry['errorcode']['1.3.6.1.4.1.3375.2.2.10.13.2.1.5.' . $index];
                } elseif ($result['state'] == 3) {
                    // Looks like ALL of the VS Pool members is down.
                    $result['status'] = 2;
                    $result['error'] = $ltmVirtualServEntry['errorcode']['1.3.6.1.4.1.3375.2.2.10.13.2.1.5.' . $index];
                } else {
                    // All is good.
                    $result['status'] = 0;
                    $result['error'] = '';
                }
            }

            // Do we have any results
            if (count($result) > 0) {
                // Let's log some debugging
                d_echo("\n\n" . $result['type'] . ': ' . $result['label'] . "\n");
                d_echo('    Status:  ' . $result['status'] . "\n");
                d_echo('    Message: ' . $result['error'] . "\n");

                // Add this result to the master array.
                $tblBigIP[] = $result;
            }
        }
    }

    // Process the BWC
    if (is_array($ltmBwcEntry['name'])) {
        foreach ($ltmBwcEntry['name'] as $oid => $value) {
            $result = [];

            // Find all Bandwidth Controller  names and UID's, then we can find everything else we need.
            if (strpos($oid, '1.3.6.1.4.1.3375.2.2.13.1.3.1.1.') !== false) {
                [$null, $index] = explode('1.3.6.1.4.1.3375.2.2.13.1.3.1.1.', $oid);
                $result['type'] = 'f5-ltm-bwc';
                $result['UID'] = (string) $index;
                $result['label'] = $value;
                // The UID is far too long to have in a RRD filename, use a hash of it instead.
                $result['hash'] = hash('crc32', $result['UID']);
            }

            // Do we have any results
            if (count($result) > 0) {
                // Add this result to the master array.
                $tblBigIP[] = $result;
            }
        }
    }

    // Process the Pools
    if (is_array($ltmPoolEntry['name'])) {
        foreach ($ltmPoolEntry['name'] as $oid => $value) {
            $result = [];

            // Find all Pool names and UID's, then we can find everything else we need.
            if (strpos($oid, '1.3.6.1.4.1.3375.2.2.5.1.2.1.1.') !== false) {
                [$null, $index] = explode('1.3.6.1.4.1.3375.2.2.5.1.2.1.1.', $oid);
                $result['type'] = 'f5-ltm-pool';
                $result['UID'] = (string) $index;
                $result['label'] = $value;
                // The UID is far too long to have in a RRD filename, use a hash of it instead.
                $result['hash'] = hash('crc32', $result['UID']);

                // Now that we have our UID we can pull all the other data we need.
                $result['mode'] = $ltmPoolEntry['mode']['1.3.6.1.4.1.3375.2.2.5.1.2.1.2.' . $index];
                $result['minup'] = $ltmPoolEntry['minup']['1.3.6.1.4.1.3375.2.2.5.1.2.1.4.' . $index];
                $result['minupstatus'] = $ltmPoolEntry['minupstatus']['1.3.6.1.4.1.3375.2.2.5.1.2.1.5.' . $index];
                $result['currentup'] = $ltmPoolEntry['currentup']['1.3.6.1.4.1.3375.2.2.5.1.2.1.8.' . $index];
                $result['minupaction'] = $ltmPoolEntry['minupaction']['1.3.6.1.4.1.3375.2.2.5.1.2.1.6.' . $index];
                $result['monitor'] = $ltmPoolEntry['monitor']['1.3.6.1.4.1.3375.2.2.5.1.2.1.17.' . $index];

                // If we have less pool members than the minimum, we should error.
                if ($result['currentup'] < $result['minup']) {
                    // Danger Will Robinson... We dont have enough Pool Members!
                    $result['status'] = 2;
                    $result['error'] = 'Minimum Pool Members not met. Action taken: ' . $error_poolaction[$result['minupaction']];
                } else {
                    // All is good.
                    $result['status'] = 0;
                    $result['error'] = '';
                }
            }

            // Do we have any results
            if (count($result) > 0) {
                // Let's log some debugging
                d_echo("\n\n" . $result['type'] . ': ' . $result['label'] . "\n");
                d_echo('    Status:            ' . $result['status'] . "\n");
                d_echo('    Message:           ' . $result['error'] . "\n");

                // Add this result to the master array.
                $tblBigIP[] = $result;
            }
        }
    }

    // Process the Pool Members
    if (is_array($ltmPoolMemberEntry['name'])) {
        foreach ($ltmPoolMemberEntry['name'] as $oid => $value) {
            $result = [];

            // Find all Pool member names and UID's, then we can find everything else we need.
            if (strpos($oid, '1.3.6.1.4.1.3375.2.2.5.3.2.1.1.') !== false) {
                [$null, $index] = explode('1.3.6.1.4.1.3375.2.2.5.3.2.1.1.', $oid);
                $result['type'] = 'f5-ltm-poolmember';
                $result['UID'] = (string) $index;
                $result['label'] = $value;
                // The UID is far too long to have in a RRD filename, use a hash of it instead.
                $result['hash'] = hash('crc32', $result['UID']);

                //Remove route domain ID from v4 IPs
                if (strlen($ltmPoolMemberEntry['ip']['1.3.6.1.4.1.3375.2.2.5.3.2.1.3.' . $index]) == 23) {
                    $ltmPoolMemberEntry['ip']['1.3.6.1.4.1.3375.2.2.5.3.2.1.3.' . $index] = substr($ltmPoolMemberEntry['ip']['1.3.6.1.4.1.3375.2.2.5.3.2.1.3.' . $index], 0, 11);
                }

                // Now that we have our UID we can pull all the other data we need.
                $result['IP'] = IP::fromHexString($ltmPoolMemberEntry['ip']['1.3.6.1.4.1.3375.2.2.5.3.2.1.3.' . $index], true);
                $result['port'] = $ltmPoolMemberEntry['port']['1.3.6.1.4.1.3375.2.2.5.3.2.1.4.' . $index];
                $result['ratio'] = $ltmPoolMemberEntry['ratio']['1.3.6.1.4.1.3375.2.2.5.3.2.1.6.' . $index];
                $result['weight'] = $ltmPoolMemberEntry['weight']['1.3.6.1.4.1.3375.2.2.5.3.2.1.7.' . $index];
                $result['priority'] = $ltmPoolMemberEntry['priority']['1.3.6.1.4.1.3375.2.2.5.3.2.1.8.' . $index];
                $result['state'] = $ltmPoolMemberEntry['state']['1.3.6.1.4.1.3375.2.2.5.6.2.1.5.' . $index];
                $result['available'] = $ltmPoolMemberEntry['available']['1.3.6.1.4.1.3375.2.2.5.6.2.1.6.' . $index];
                $result['nodename'] = $ltmPoolMemberEntry['nodename']['1.3.6.1.4.1.3375.2.2.5.3.2.1.19.' . $index];

                // If available and bad state
                // 0 = None, 1 = Green, 2 = Yellow, 3 = Red, 4 = Blue
                if (($result['available'] == 1) && ($result['state'] == 3)) {
                    // Warning Alarm, the pool member is down.
                    $result['status'] = 1;
                    $result['error'] = 'Pool Member is Down: ' . $ltmPoolMemberEntry['errorcode']['1.3.6.1.4.1.3375.2.2.5.6.2.1.8.' . $index];
                } else {
                    // All is good.
                    $result['status'] = 0;
                    $result['error'] = '';
                }
            }

            // Do we have any results
            if (count($result) > 0) {
                // Let's log some debugging
                d_echo("\n\n" . $result['type'] . ': ' . $result['label'] . "\n");
                d_echo('    Status:   ' . $result['status'] . "\n");
                d_echo('    Message:  ' . $result['error'] . "\n");

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

        if (! $component_key) {
            // The component doesn't exist, we need to ADD it - ADD.
            $new_component = $component->createComponent($device['device_id'], $array['type']);
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
            echo '-';
            $component->deleteComponent($key);
        }
    }

    // Write the Components back to the DB.
    $component->setComponentPrefs($device['device_id'], $components);
    echo "\n";
}// End if not error
