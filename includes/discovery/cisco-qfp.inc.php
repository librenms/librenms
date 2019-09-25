<?php
/*
 * LibreNMS module to capture Cisco QFP Statistics
 *
 * Copyright (c) 2019 Pavle Obradovic <pobradovic08@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os_group'] == 'cisco') {
    $module = 'cisco-qfp';

    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id'], array('type'=>$module));
    $components = $components[$device['device_id']];

    $qfp_general_data = snmpwalk_group($device, 'ceqfpSystemTable', 'CISCO-ENTITY-QFP-MIB');

    if ($qfp_general_data) {

        // Loop through SNMP data and add or update components
        foreach ($qfp_general_data as $qfp_index => $data) {

            // Component data
            $component_data = array(
                'label' => 'qfp_' . $qfp_index,
                'entPhysicalIndex' => $qfp_index
            );

            // Find existing component ID if QFP already exists
            $component_id = false;
            foreach ($components as $tmp_component_id => $tmp_component) {
                if ($tmp_component['entPhysicalIndex'] == $qfp_index) {
                    $component_id = $tmp_component_id;
                }
            }

            /*
             * If $component_id is false QFP Component doesn't exist
             * Create new component and add it to $components array
             */
            if (!$component_id) {
                $new_component = $component->createComponent($device['device_id'], $module);
                $component_id = key($new_component);
                $components[$component_id] = array_merge($new_component[$component_id], $component_data);
                echo '+';
            } else {
                $components[$component_id] = array_merge($components[$component_id], $component_data);
                echo '.';
            }
        }

        /*
         * Loop trough components, check against SNMP QFP indexes and delete if needed
         */
        foreach ($components as $tmp_component_id => $tmp_component) {
            $found = in_array($tmp_component['entPhysicalIndex'], array_keys($qfp_general_data));
            if (!$found) {
                $component->deleteComponent($tmp_component_id);
                echo '-';
            }
        }
    }
    
    $component->setComponentPrefs($device['device_id'], $components);
    echo "\n";
}
