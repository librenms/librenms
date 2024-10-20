	<?php
/*
 * LibreNMS module to capture QoS Details
 *
 * Copyright (c) 2024 Steven Wilton <swilton@fluentit.com.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use App\Models\Device;
use LibreNMS\RRD\RrdDefinition;

function pollRouterosQueueTrees (array $device) {
    $poll_time = time();
    $dbQueues = Device::find($device['device_id'])->qos()
        ->where('type', 'routeros_tree')
        ->where('disabled', '0')
        ->where('ignore', '0')
        ->get();

    if ($dbQueues) {
        // Fetch all SNMP data
        $snmpBytesSent = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.2.1.7');
        $snmpBytesDrop = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.2.1.9');

        foreach ($dbQueues as $dbQueue) {
            $idx = $dbQueue->snmp_idx;

            $tags['rrd_name'] = ['routeros-queuetree', $dbQueue->rrd_id];
            $tags['rrd_def'] = RrdDefinition::make()
                ->addDataset('sentbytes', 'COUNTER', 0)
                ->addDataset('dropbytes', 'COUNTER', 0);

            d_echo("\n\nComponent RouterOS-QueueTree: " . $dbQueue->title . "\n");
            d_echo('    SentBytes: ' . $snmpBytesSent['1.3.6.1.4.1.14988.1.1.2.2.1.7'][$idx] . "\n");
            d_echo('    DropBytes: ' . $snmpBytesDrop['1.3.6.1.4.1.14988.1.1.2.2.1.9'][$idx] . "\n");

            $fields = [
                'sentbytes' => $snmpBytesSent['1.3.6.1.4.1.14988.1.1.2.2.1.7'][$idx],
                'dropbytes' => $snmpBytesDrop['1.3.6.1.4.1.14988.1.1.2.2.1.9'][$idx],
            ];

            data_update($device, 'qos', $tags, $fields);

            // Update the DB Object with bytes
            if (! is_null($dbQueue->last_polled) && $dbQueue->last_polled < $poll_time) {
                $poll_interval = $poll_time - $dbQueue->last_polled;
                $traffic_out_rate = intval(($snmpBytesSent['1.3.6.1.4.1.14988.1.1.2.2.1.7'][$idx] - $dbQueue->last_traffic_out) / $poll_interval);
                $drop_out_rate = intval(($snmpBytesDrop['1.3.6.1.4.1.14988.1.1.2.2.1.9'][$idx] - $dbQueue->last_drop_out) / $poll_interval);

                $dbQueue->traffic_out_rate = $traffic_out_rate >= 0 ? $traffic_out_rate : 0;
                $dbQueue->drop_out_rate = $drop_out_rate >= 0 ? $drop_out_rate : 0;
            }

            // Update output counters
            $dbQueue->last_polled = $poll_time;
            $dbQueue->last_traffic_out = $snmpBytesSent['1.3.6.1.4.1.14988.1.1.2.2.1.7'][$idx];
            $dbQueue->last_drop_out = $snmpBytesDrop['1.3.6.1.4.1.14988.1.1.2.2.1.9'][$idx];

            // Make sure all in rates are null
            $dbQueue->last_traffic_in = null;
            $dbQueue->last_drop_in = null;
            $dbQueue->traffic_in_rate = null;
            $dbQueue->drop_in_rate = null;

            $dbQueue->save();
        }
    }
}

function pollRouterosSimpleQueues (array $device) {
    $poll_time = time();
    $dbQueues = Device::find($device['device_id'])->qos()
        ->where('type', 'routeros_simple')
        ->where('disabled', '0')
        ->where('ignore', '0')
        ->get();

    if ($dbQueues) {
        // Fetch all SNMP data
        $snmpBytesSentIn = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.8');
        $snmpBytesSentOut = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.9');
        $snmpBytesDropIn = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.14');
        $snmpBytesDropOut = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.15');

        foreach ($dbQueues as $rowId => $dbQueue) {
            $idx = $dbQueue->snmp_idx;

            $tags['rrd_name'] = ['routeros-simplequeue', $dbQueue->rrd_id];
            $tags['rrd_def'] = RrdDefinition::make()
                ->addDataset('sentbytesin', 'COUNTER', 0)
                ->addDataset('sentbytesout', 'COUNTER', 0)
                ->addDataset('dropbytesin', 'COUNTER', 0)
                ->addDataset('dropbytesout', 'COUNTER', 0);

            d_echo("\n\nComponent RouterOS-SimpleQueue: " . $dbQueue->title . "\n");
            d_echo('    SentBytesIn: ' . $snmpBytesSentIn['1.3.6.1.4.1.14988.1.1.2.1.1.8'][$idx] . "\n");
            d_echo('    SentBytesOut: ' . $snmpBytesSentOut['1.3.6.1.4.1.14988.1.1.2.1.1.9'][$idx] . "\n");
            d_echo('    DropBytesIn: ' . $snmpBytesDropIn['1.3.6.1.4.1.14988.1.1.2.1.1.14'][$idx] . "\n");
            d_echo('    DropBytesOut: ' . $snmpBytesDropOut['1.3.6.1.4.1.14988.1.1.2.1.1.15'][$idx] . "\n");

            $fields = [
                'sentbytesin' => $snmpBytesSentIn['1.3.6.1.4.1.14988.1.1.2.1.1.8'][$idx],
                'sentbytesout' => $snmpBytesSentOut['1.3.6.1.4.1.14988.1.1.2.1.1.9'][$idx],
                'dropbytesin' => $snmpBytesDropIn['1.3.6.1.4.1.14988.1.1.2.1.1.14'][$idx],
                'dropbytesout' => $snmpBytesDropOut['1.3.6.1.4.1.14988.1.1.2.1.1.15'][$idx],
            ];

            data_update($device, 'qos', $tags, $fields);

            // Update the DB Object with bytes
            if (! is_null($dbQueue->last_polled) && $dbQueue->last_polled < $poll_time) {
                $poll_interval = $poll_time - $dbQueue->last_polled;
                $traffic_in_rate = intval(($snmpBytesSentIn['1.3.6.1.4.1.14988.1.1.2.1.1.8'][$idx] - $dbQueue->last_traffic_in) / $poll_interval);
                $traffic_out_rate = intval(($snmpBytesSentOut['1.3.6.1.4.1.14988.1.1.2.1.1.9'][$idx] - $dbQueue->last_traffic_out) / $poll_interval);
                $drop_in_rate = intval(($snmpBytesDropIn['1.3.6.1.4.1.14988.1.1.2.1.1.14'][$idx] - $dbQueue->last_drop_in) / $poll_interval);
                $drop_out_rate = intval(($snmpBytesDropOut['1.3.6.1.4.1.14988.1.1.2.1.1.15'][$idx] - $dbQueue->last_drop_out) / $poll_interval);

                $dbQueue->traffic_in_rate = $traffic_in_rate >= 0 ? $traffic_in_rate : 0;
                $dbQueue->traffic_out_rate = $traffic_out_rate >= 0 ? $traffic_out_rate : 0;
		$dbQueue->drop_in_rate = $drop_in_rate >= 0 ? $drop_in_rate : 0;
                $dbQueue->drop_out_rate = $drop_out_rate >= 0 ? $drop_out_rate : 0;
            }

            // Update counters
            $dbQueue->last_polled = $poll_time;
            $dbQueue->last_traffic_in = $snmpBytesSentIn['1.3.6.1.4.1.14988.1.1.2.1.1.8'][$idx];
            $dbQueue->last_traffic_out = $snmpBytesSentOut['1.3.6.1.4.1.14988.1.1.2.1.1.9'][$idx];
            $dbQueue->last_drop_in = $snmpBytesDropIn['1.3.6.1.4.1.14988.1.1.2.1.1.14'][$idx];
            $dbQueue->last_drop_out = $snmpBytesDropOut['1.3.6.1.4.1.14988.1.1.2.1.1.15'][$idx];

            $dbQueue->save();
        }
    }
}

if ($device['os'] == 'routeros') {
    pollRouterosSimpleQueues($device);
    pollRouterosQueueTrees($device);
}
