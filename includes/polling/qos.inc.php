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

use LibreNMS\RRD\RrdDefinition;

function pollRouterosQueueTrees (array $device) {
    $component = new LibreNMS\Component();
    $options['filter']['type'] = ['=', 'RouterOS-QueueTree'];
    $options['filter']['disabled'] = ['=', 0];
    $options['filter']['ignore'] = ['=', 0];
    $dbQueues = $component->getComponents($device['device_id'], $options);
    $dbQueues = $dbQueues[$device['device_id']];

    if ($dbQueues) {
        // Fetch all SNMP data
        $snmpBytesSent = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.2.1.7');
        $snmpBytesDrop = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.2.1.9');

        foreach ($dbQueues as $rowId => $dbQueue) {
            $qtid = $dbQueue['qt-id'];

            $tags['rrd_name'] = ['routeros-queuetree', $dbQueue['qt-name']];
            $tags['rrd_def'] = RrdDefinition::make()
                ->addDataset('sentbytes', 'COUNTER', 0)
                ->addDataset('dropbytes', 'COUNTER', 0);

            d_echo("\n\nComponent RouterOS-QueueTree: " . $dbQueue['qt-name'] . "\n");
            d_echo('    SentBytes: ' . $snmpBytesSent['1.3.6.1.4.1.14988.1.1.2.2.1.7'][$qtid] . "\n");
            d_echo('    DropBytes: ' . $snmpBytesDrop['1.3.6.1.4.1.14988.1.1.2.2.1.9'][$qtid] . "\n");

            $fields = [
                'sentbytes' => $snmpBytesSent['1.3.6.1.4.1.14988.1.1.2.2.1.7'][$qtid],
                'dropbytes' => $snmpBytesDrop['1.3.6.1.4.1.14988.1.1.2.2.1.9'][$qtid],
            ];

            data_update($device, 'qos', $tags, $fields);
        }
    }
}

function pollRouterosSimpleQueues (array $device) {
    $component = new LibreNMS\Component();
    $options['filter']['type'] = ['=', 'RouterOS-SimpleQueue'];
    $options['filter']['disabled'] = ['=', 0];
    $options['filter']['ignore'] = ['=', 0];
    $dbQueues = $component->getComponents($device['device_id'], $options);
    $dbQueues = $dbQueues[$device['device_id']];

    if ($dbQueues) {
        // Fetch all SNMP data
        $snmpBytesSentIn = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.8');
        $snmpBytesSentOut = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.9');
        $snmpBytesDropIn = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.14');
        $snmpBytesDropOut = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.15');

        foreach ($dbQueues as $rowId => $dbQueue) {
            $sqid = $dbQueue['sq-id'];

            $tags['rrd_name'] = ['routeros-simplequeue', $dbQueue['sq-name']];
            $tags['rrd_def'] = RrdDefinition::make()
                ->addDataset('sentbytesin', 'COUNTER', 0)
                ->addDataset('sentbytesout', 'COUNTER', 0)
                ->addDataset('dropbytesin', 'COUNTER', 0)
                ->addDataset('dropbytesout', 'COUNTER', 0);

            d_echo("\n\nComponent RouterOS-SimpleQueue: " . $dbQueue['sq-name'] . "\n");
            d_echo('    SentBytesIn: ' . $snmpBytesSentIn['1.3.6.1.4.1.14988.1.1.2.1.1.8'][$sqid] . "\n");
            d_echo('    SentBytesOut: ' . $snmpBytesSentOut['1.3.6.1.4.1.14988.1.1.2.1.1.9'][$sqid] . "\n");
            d_echo('    DropBytesIn: ' . $snmpBytesDropIn['1.3.6.1.4.1.14988.1.1.2.1.1.14'][$sqid] . "\n");
            d_echo('    DropBytesOut: ' . $snmpBytesDropOut['1.3.6.1.4.1.14988.1.1.2.1.1.15'][$sqid] . "\n");

            $fields = [
                'sentbytesin' => $snmpBytesSentIn['1.3.6.1.4.1.14988.1.1.2.1.1.8'][$sqid],
                'sentbytesout' => $snmpBytesSentOut['1.3.6.1.4.1.14988.1.1.2.1.1.9'][$sqid],
                'dropbytesin' => $snmpBytesDropIn['1.3.6.1.4.1.14988.1.1.2.1.1.14'][$sqid],
                'dropbytesout' => $snmpBytesDropOut['1.3.6.1.4.1.14988.1.1.2.1.1.15'][$sqid],
            ];

            data_update($device, 'qos', $tags, $fields);
        }
    }
}

if ($device['os'] == 'routeros') {
    pollRouterosSimpleQueues($device);
    pollRouterosQueueTrees($device);
}
