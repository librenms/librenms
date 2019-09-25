<?php
/*
 * Polling module to get statistics from Cisco QFP forwarding processor
 *
 * Copyright (c) 2019 Pavle Obradovic <pobradovic08@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

$module = 'cisco-qfp';


/*
 * Fetch device components and filter ignored or disabled ones
 */
$component = new LibreNMS\Component();

$options = array(
    'filter' => array(
        'type' => array('=', $module),
        'disabled' => array('=', 0),
        'ignore' => array('=', 0)
    )
);

$components = $component->getComponents($device['device_id'], $options);
$components = $components[$device['device_id']];

/*
 * SNMP makes available multiple datapoints dependnet on the time interval
 * Use 5min for now but if in future LibreNMS polling interval is set through
 * config file we can use this to quickly select best time interval
 */
$time_interval_array = array(
    '5sec' => 1,
    '1min' => 2,
    '5min' => 3,
    '1h' => 4
);

$ti = $time_interval_array['5min'];


if (!empty($components) && is_array($components)) {
    foreach ($components as $component_id => $component) {
        $qfp_index = $component['entPhysicalIndex'];
        $util_oid_suffix = $qfp_index . '.' . $ti;
        $utilization_oids = array(
            'InPriorityPps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.2.' . $util_oid_suffix,
            'InPriorityBps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.3.' . $util_oid_suffix,
            'InNonPriorityPps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.4.' . $util_oid_suffix,
            'InNonPriorityBps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.5.' . $util_oid_suffix,
            'InTotalPps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.6.' . $util_oid_suffix,
            'InTotalBps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.7.' . $util_oid_suffix,
            'OutPriorityPps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.8.' . $util_oid_suffix,
            'OutPriorityBps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.9.' . $util_oid_suffix,
            'OutNonPriorityPps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.10.' . $util_oid_suffix,
            'OutNonPriorityBps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.11.' . $util_oid_suffix,
            'OutTotalPps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.12.' . $util_oid_suffix,
            'OutTotalBps' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.13.' . $util_oid_suffix,
            'ProcessingLoad' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.14.' . $util_oid_suffix
        );

        $mem_oid_suffix = $qfp_index . '.1';
        $memory_oids = array(
            '.1.3.6.1.4.1.9.9.715.1.1.7.1.6.' . $mem_oid_suffix,    //RisingThreshold
            '.1.3.6.1.4.1.9.9.715.1.1.7.1.7.' . $mem_oid_suffix,    //FallingThreshold
            '.1.3.6.1.4.1.9.9.715.1.1.7.1.15.' . $mem_oid_suffix,   //LowFreeWatermark
            '.1.3.6.1.4.1.9.9.715.1.1.7.1.9.' . $mem_oid_suffix,    //Total
            '.1.3.6.1.4.1.9.9.715.1.1.7.1.11.' . $mem_oid_suffix,   //InUse
            '.1.3.6.1.4.1.9.9.715.1.1.7.1.13.' . $mem_oid_suffix    //Free
        );

        $utilization_data = snmp_get_multi_oid($device, array_values($utilization_oids));
        $memory_data = snmp_get_multi_oid($device, $memory_oids);

        //TODO: Update components with general data

        /*
         * Create RRDs
         */
        $rrd_name = array($module, 'util', $qfp_index);
        $rrd_def = RrdDefinition::make();
        foreach ($utilization_oids as $name => $oid) {
            $rrd_def->addDataset($name, 'GAUGE', 0);
            $rrd[$name] = $utilization_data[$utilization_oids[$name]];
        }
        $tags = compact('module', 'rrd_name', 'rrd_def', 'qfp_index');
        data_update($device, $module, $tags, $rrd);
        unset($filename, $rrd_filename, $rrd_name, $rrd_def, $rrd);
    }
}


unset($module, $component, $components);

