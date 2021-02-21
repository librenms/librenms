<?php
/**
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * LibreNMS module to capture Cisco QFP Statistics
 *
 * @link       https://www.librenms.org
 * @copyright  2019 LibreNMS
 * @author     Pavle Obradovic <pobradovic08@gmail.com>
 */
if ($device['os_group'] == 'cisco') {
    $module = 'cisco-qfp';

    /*
     * CISCO-ENTITY-QFP-MIB::ceqfpSystemState values
     */
    $system_states = [
        1 => 'unknown',
        2 => 'reset',
        3 => 'init',
        4 => 'active',
        5 => 'activeSolo',
        6 => 'standby',
        7 => 'hotStandby',
    ];

    /*
     * CISCO-ENTITY-QFP-MIB::ceqfpSystemTrafficDirection values
     */
    $system_traffic_direction = [
        1 => 'none',
        2 => 'ingress',
        3 => 'egress',
        4 => 'both',
    ];

    /*
     * Get module's components for a device
     */
    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id'], ['type'=>$module]);
    $components = $components[$device['device_id']];

    /*
     * Walk through CISCO-ENTITY-QFP-MIB::ceqfpSystemTable
     */
    $qfp_general_data = snmpwalk_group($device, 'ceqfpSystemTable', 'CISCO-ENTITY-QFP-MIB');
    if ($qfp_general_data) {
        /*
         * Loop through SNMP data and add or update components
         */
        foreach ($qfp_general_data as $qfp_index => $data) {
            /*
             * Get entPhysicalName for QFP
             */
            $qfp_name_oid = '.1.3.6.1.2.1.47.1.1.1.1.7.' . $qfp_index;
            $qfp_name_data = snmp_get_multi_oid($device, [$qfp_name_oid]);
            $qfp_name = $qfp_name_data[$qfp_name_oid];

            /*
             * Component data array for `component_prefs`
             */
            $component_data = [
                'label' => 'qfp_' . $qfp_index,
                'entPhysicalIndex' => $qfp_index,
                'name' => $qfp_name,
                'traffic_direction' => $system_traffic_direction[$data['ceqfpSystemTrafficDirection']],
                'system_state' => $system_states[$data['ceqfpSystemState']],
                'system_loads' => $data['ceqfpNumberSystemLoads'],
                'system_last_load' => $data['ceqfpSystemLastLoadTime'],
            ];

            /*
             * Find existing component ID if QFP is already known
             */
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
            if (! $component_id) {
                $new_component = $component->createComponent($device['device_id'], $module);
                $component_id = key($new_component);
                $components[$component_id] = array_merge($new_component[$component_id], $component_data);
                echo '+';
            } else {
                $components[$component_id] = array_merge($components[$component_id], $component_data);
                echo '.';
            }
        }
    }

    /*
     * Loop trough components, check against SNMP QFP indexes and delete if needed
     */
    foreach ($components as $tmp_component_id => $tmp_component) {
        $found = in_array($tmp_component['entPhysicalIndex'], array_keys($qfp_general_data));
        if (! $found) {
            $component->deleteComponent($tmp_component_id);
            echo '-';
        }
    }

    /*
     * Save components
     */
    $component->setComponentPrefs($device['device_id'], $components);

    echo "\n";
}
