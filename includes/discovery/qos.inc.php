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

function syncQueues(int $device_id, string $module, array $snmpQueues, string $idPrefName)
{
    // Fetch all compunents for this device
    $component = new LibreNMS\Component();
    $dbQueueArray = $component->getComponents($device_id, ['type' => $module]);
    $dbQueues = $dbQueueArray[$device_id];

    if (! is_null($dbQueues)) {
        d_echo(count($dbQueues) . " existing queues found in DB\n");
    }

    // Add any missing queues to the DB, keeping track of all db positions we used
    $dbKeepRows = [];
    foreach ($snmpQueues as $snmpIndex => $snmpQueue) {
        $dbQueuePos = null;
        // Loop over our components to determine if the component exists, or we need to add it.
        foreach ($dbQueues as $thisDbQueuePos => $thisDbQueue) {
            if ($snmpQueue[$idPrefName] === $thisDbQueue[$idPrefName]) {
                $dbQueuePos = $thisDbQueuePos;
                break;
            }
        }

        if (is_null($dbQueuePos)) {
            $newDbQueue = $component->createComponent($device_id, $module);
            $dbQueuePos = key($newDbQueue);
            $dbQueues[$dbQueuePos] = array_merge($newDbQueue[$dbQueuePos], $snmpQueue);
            echo '+';
        } else {
            $dbQueues[$dbQueuePos] = array_merge($dbQueues[$dbQueuePos], $snmpQueue);
            echo '.';
        }
        $dbKeepRows[strval($dbQueuePos)] = true;
    }

    /*
    * Loop over the dbQueues array to delete unused rows
    */
    foreach ($dbQueues as $dbQueuePos => $dbQueue) {
        if (! array_key_exists(strval($dbQueuePos), $dbKeepRows)) {
            echo '-';
            $component->deleteComponent($dbQueuePos);
        }
    }

    // Write the Components back to the DB.
    $component->setComponentPrefs($device_id, $dbQueues);
    echo "\n";
}

function discoverRouterosQueueTree(array $device)
{
    // Keep track of queues so we can sync at the end
    $snmpQueues = [];

    // Fetch relevant table
    $queueNames = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.2.1.2');
    $queueMarks = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.2.1.3');
    $queueParents = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.2.1.4');

    /*
     * If we got null, an error occured.  Print error and return so we don't delete everything
     */
    if (is_null($queueNames) || is_null($queueMarks) || is_null($queueParents)) {
        echo "Error fetching RouterOS queue tree data\n";

        return;
    }

    d_echo(count($queueNames) . " queue trees found:\n");

    foreach ($queueNames['1.3.6.1.4.1.14988.1.1.2.2.1.2'] as $qtid => $qtname) {
        $snmpQueues[$qtid]['qt-id'] = strval($qtid);
        $snmpQueues[$qtid]['qt-name'] = $qtname;
        $snmpQueues[$qtid]['qt-mark'] = $queueMarks['1.3.6.1.4.1.14988.1.1.2.2.1.3'][$qtid];
        $snmpQueues[$qtid]['qt-parent'] = $queueParents['1.3.6.1.4.1.14988.1.1.2.2.1.4'][$qtid];

        d_echo("\nQueue Tree" . $sqid . ' name: ' . $sqname . "\n");
        d_echo('Packet Mark: ' . $queueMarks['1.3.6.1.4.1.14988.1.1.2.2.1.3'][$qtid] . "\n");
        d_echo('Parent     : ' . $queueParents['1.3.6.1.4.1.14988.1.1.2.2.1.4'][$qtid] . "\n");
    }

    syncQueues($device['device_id'], 'RouterOS-QueueTree', $snmpQueues, 'qt-name');
}

function discoverRouterosSimpleQueue(array $device)
{
    // Keep track of queues so we can sync at the end
    $snmpQueues = [];

    // Fetch relevant table
    $queueNames = snmpwalk_array_num($device, '.1.3.6.1.4.1.14988.1.1.2.1.1.2');

    /*
     * If we got null, an error occured.  Print error and return so we don't delete everything
     */
    if (is_null($queueNames)) {
        echo "Error fetching RouterOS simple queue data\n";

        return;
    }

    d_echo(count($queueNames) . " Simple queues found:\n");

    foreach ($queueNames['1.3.6.1.4.1.14988.1.1.2.1.1.2'] as $sqid => $sqname) {
        $snmpQueues[$sqid]['sq-id'] = strval($sqid);
        $snmpQueues[$sqid]['sq-name'] = $sqname;

        d_echo("\nSimple Queue " . $sqid . ' name: ' . $sqname . "\n");
    }

    syncQueues($device['device_id'], 'RouterOS-SimpleQueue', $snmpQueues, 'sq-name');
}

if ($device['os'] == 'routeros') {
    discoverRouterosSimpleQueue($device);
    discoverRouterosQueueTree($device);
}
