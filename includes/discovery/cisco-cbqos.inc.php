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

if ($device['os_group'] == 'cisco') {

    $MODULE = 'Cisco-CBQOS';
    echo $MODULE.': ';

    require_once 'includes/component.php';
    $COMPONENT = new component();
    $COMPONENTS = $COMPONENT->getComponents($device['device_id'],array('type'=>$MODULE));

    // We only care about our device id.
    $COMPONENTS = $COMPONENTS[$device['device_id']];


    // Begin our master array, all other values will be processed into this array.
    $tblCBQOS = array();

    // Let's gather some data..
    $tblcbQosServicePolicy = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.166.1.1');
    $tblcbQosObjects = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.166.1.5', 2);
    $tblcbQosPolicyMapCfg = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.166.1.6');
    $tblcbQosClassMapCfg = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.166.1.7');
    $tblcbQosMatchStmtCfg = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.166.1.8');

    /*
     * False == no object found - this is not an error, there is no QOS configured
     * null  == timeout or something else that caused an error, there may be QOS configured but we couldn't get it.
     */
    if ( is_null($tblcbQosServicePolicy) || is_null($tblcbQosObjects) || is_null($tblcbQosPolicyMapCfg) || is_null($tblcbQosClassMapCfg) || is_null($tblcbQosMatchStmtCfg) ) {
        // We have to error here or we will end up deleting all our QoS components.
        echo "Error\n";
    }
    else {
        // No Error, lets process things.
        d_echo("QoS Objects Found:\n");

        foreach ($tblcbQosObjects['1.3.6.1.4.1.9.9.166.1.5.1.1.2'] as $spid => $array) {

            foreach ($array as $spobj => $index) {
                $RESULT = array();

                // Produce a unique reproducible index for this entry.
                $RESULT['UID'] = hash('crc32', $spid."-".$spobj);

                // Now that we have a valid identifiers, lets add some more data
                $RESULT['sp-id'] = $spid;
                $RESULT['sp-obj'] = $spobj;

                // Add the Type, Policy-map, Class-map, etc.
                $type = $tblcbQosObjects['1.3.6.1.4.1.9.9.166.1.5.1.1.3'][$spid][$spobj];
                $RESULT['qos-type'] = $type;

                // Add the Parent, this lets us work out our hierarchy for display later.
                $RESULT['parent'] = $tblcbQosObjects['1.3.6.1.4.1.9.9.166.1.5.1.1.4'][$spid][$spobj];
                $RESULT['direction'] = $tblcbQosServicePolicy['1.3.6.1.4.1.9.9.166.1.1.1.1.3'][$spid];
                $RESULT['ifindex'] = $tblcbQosServicePolicy['1.3.6.1.4.1.9.9.166.1.1.1.1.4'][$spid];

                // Gather different data depending on the type.
                switch ($type) {
                    case 1:
                        // Policy-map, get data from that table.
                        d_echo("\nIndex: ".$index."\n");
                        d_echo("    UID: ".$RESULT['UID']."\n");
                        d_echo("    SPID.SPOBJ: ".$RESULT['sp-id'].".".$RESULT['sp-obj']."\n");
                        d_echo("    If-Index: ".$RESULT['ifindex']."\n");
                        d_echo("    Type: 1 - Policy-Map\n");
                        $RESULT['label'] = $tblcbQosPolicyMapCfg['1.3.6.1.4.1.9.9.166.1.6.1.1.1'][$index];
                        if ($tblcbQosPolicyMapCfg['1.3.6.1.4.1.9.9.166.1.6.1.1.2'][$index] != "") {
                            $RESULT['label'] .= " - ".$tblcbQosPolicyMapCfg['1.3.6.1.4.1.9.9.166.1.6.1.1.2'][$index];
                        }
                        d_echo("    Label: ".$RESULT['label']."\n");
                        break;
                    case 2:
                        // Class-map, get data from that table.
                        d_echo("\nIndex: ".$index."\n");
                        d_echo("    UID: ".$RESULT['UID']."\n");
                        d_echo("    SPID.SPOBJ: ".$RESULT['sp-id'].".".$RESULT['sp-obj']."\n");
                        d_echo("    If-Index: ".$RESULT['ifindex']."\n");
                        d_echo("    Type: 2 - Class-Map\n");
                        $RESULT['label'] = $tblcbQosClassMapCfg['1.3.6.1.4.1.9.9.166.1.7.1.1.1'][$index];
                        if($tblcbQosClassMapCfg['1.3.6.1.4.1.9.9.166.1.7.1.1.2'][$index] != "") {
                            $RESULT['label'] .= " - ".$tblcbQosClassMapCfg['1.3.6.1.4.1.9.9.166.1.7.1.1.2'][$index];
                        }
                        d_echo("    Label: ".$RESULT['label']."\n");
                        if ($tblcbQosClassMapCfg['1.3.6.1.4.1.9.9.166.1.7.1.1.3'][$index] == 2) {
                            $RESULT['map-type'] = 'Match-All';
                        }
                        elseif ($tblcbQosClassMapCfg['1.3.6.1.4.1.9.9.166.1.7.1.1.3'][$index] == 3) {
                            $RESULT['map-type'] = 'Match-Any';
                        }
                        else {
                            $RESULT['map-type'] = 'None';
                        }

                        // Find a child, this will be a type 3
                        foreach ($tblcbQosObjects['1.3.6.1.4.1.9.9.166.1.5.1.1.4'][$spid] as $ID => $VALUE) {
                            if ($VALUE == $RESULT['sp-obj']) {
                                // We have our child, import the match
                                if ($tblcbQosObjects['1.3.6.1.4.1.9.9.166.1.5.1.1.3'][$spid][$ID] == 3) {
                                    $RESULT['match'] = $RESULT['map-type'].": ".$tblcbQosMatchStmtCfg['1.3.6.1.4.1.9.9.166.1.8.1.1.1'][$tblcbQosObjects['1.3.6.1.4.1.9.9.166.1.5.1.1.2'][$spid][$ID]];
                                    d_echo("    Match: ".$RESULT['match']."\n");
                                }
                            }
                        }
                        break;
                    default:
                        continue 2;
                }

                $tblCBQOS[] = $RESULT;
            }
        }

        /*
         * Ok, we have our 2 array's (Components and SNMP) now we need
         * to compare and see what needs to be added/updated.
         *
         * Let's loop over the SNMP data to see if we need to ADD or UPDATE any components.
         */
        foreach ($tblCBQOS as $key => $array) {
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

            foreach ($tblCBQOS as $k => $v) {
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
