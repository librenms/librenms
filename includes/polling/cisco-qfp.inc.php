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

use LibreNMS\RRD\RrdDefinition;

$module = 'cisco-qfp';

/*
 * Fetch device components and filter ignored or disabled ones
 */
$options = [
    'filter' => [
        'type' => ['=', $module],
        'disabled' => ['=', 0],
        'ignore' => ['=', 0],
    ],
];

$component = new LibreNMS\Component();
$components = $component->getComponents($device['device_id'], $options);
$components = $components[$device['device_id']];

/*
 * SNMP makes available multiple datapoints dependnet on the time interval
 * Use 5min for now but if in future LibreNMS polling interval is set through
 * config file we can use this to quickly select best time interval
 */
$time_interval_array = [
    '5sec' => 1,
    '1min' => 2,
    '5min' => 3,
    '1h' => 4,
];

$ti = $time_interval_array['5min'];

if (! empty($components) && is_array($components)) {
    foreach ($components as $component_id => $tmp_component) {
        /*
         * Build OIDs and use snmpget to fetch multiple OIDs at once instead of snmpwalk
         */
        $qfp_index = $tmp_component['entPhysicalIndex'];

        /*
         * ceqfpUtilizationEntry table has `entPhysicalIndex` and `ceqfpUtilTimeInterval` indexes
         */
        $util_oid_suffix = $qfp_index . '.' . $ti;
        $util_oids = [
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
            'ProcessingLoad' => '.1.3.6.1.4.1.9.9.715.1.1.6.1.14.' . $util_oid_suffix,
        ];

        /*
         * ceqfpMemoryResourceEntry table has `entPhysicalIndex` and `ceqfpMemoryResType` indexes
         * ceqfpMemoryResType has the only one valid value (1: dram)
         */
        $mem_oid_suffix = $qfp_index . '.1';
        $memory_oids = [
            'RisingThreshold' => '.1.3.6.1.4.1.9.9.715.1.1.7.1.6.' . $mem_oid_suffix,
            'FallingThreshold' => '.1.3.6.1.4.1.9.9.715.1.1.7.1.7.' . $mem_oid_suffix,
            'LowFreeWatermark' => '.1.3.6.1.4.1.9.9.715.1.1.7.1.15.' . $mem_oid_suffix,
            'Total' => '.1.3.6.1.4.1.9.9.715.1.1.7.1.9.' . $mem_oid_suffix,
            'InUse' => '.1.3.6.1.4.1.9.9.715.1.1.7.1.11.' . $mem_oid_suffix,
            'Free' => '.1.3.6.1.4.1.9.9.715.1.1.7.1.13.' . $mem_oid_suffix,
        ];

        /*
         * Get SNMP data
         */
        $util_data = snmp_get_multi_oid($device, array_values($util_oids));
        $memory_data = snmp_get_multi_oid($device, array_values($memory_oids));

        /*
         * Check if the oids exist
         * Possible FP linecard OIR between discovery and polling calls
         */
        if (! empty($util_data) && ! empty($memory_data)) {
            $total_packets = $util_data[$util_oids['InTotalPps']] + $util_data[$util_oids['OutTotalPps']];
            $throughput = $util_data[$util_oids['InTotalBps']] + $util_data[$util_oids['OutTotalBps']];
            $average_packet = $throughput / 8 / $total_packets;
            /*
             * Create component data array for `component_prefs`
             * and update components
             */
            $component_data = [
                'utilization' => $util_data[$util_oids['ProcessingLoad']],
                'packets' => $total_packets,
                'throughput' => $throughput,
                'average_packet' => $average_packet,
                'memory_total' => $memory_data[$memory_oids['Total']],
                'memory_used' => $memory_data[$memory_oids['InUse']],
                'memory_free' => $memory_data[$memory_oids['Free']],
            ];
            $components[$component_id] = array_merge($components[$component_id], $component_data);

            /*
             * Create Utilization RRDs
             */
            $rrd_name = [$module, 'util', $qfp_index];
            $rrd_def = RrdDefinition::make();
            foreach ($util_oids as $name => $oid) {
                $rrd_def->addDataset($name, 'GAUGE', 0);
                $rrd[$name] = $util_data[$util_oids[$name]];
            }
            $tags = compact('module', 'rrd_name', 'rrd_def', 'qfp_index');
            data_update($device, $module, $tags, $rrd);
            unset($filename, $rrd_filename, $rrd_name, $rrd_def, $rrd);

            /*
             * Create Utilization RRDs
             */
            $rrd_name = [$module, 'memory', $qfp_index];
            $rrd_def = RrdDefinition::make();
            foreach ($memory_oids as $name => $oid) {
                $rrd_def->addDataset($name, 'GAUGE', 0);
                $rrd[$name] = $memory_data[$memory_oids[$name]];
            }
            $tags = compact('module', 'rrd_name', 'rrd_def', 'qfp_index');
            data_update($device, $module, $tags, $rrd);
            unset($filename, $rrd_filename, $rrd_name, $rrd_def, $rrd);
        }
    }

    /*
     * Update DB Components
     */
    $component->setComponentPrefs($device['device_id'], $components);
}

unset($component, $components);
