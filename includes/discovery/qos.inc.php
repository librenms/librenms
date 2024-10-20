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
use App\Models\Qos;

function syncRouterosQueues(int $device_id, string $module, array $snmpQueues, bool $egress, bool $ingress)
{
    $dbQueues = Device::find($device_id)->qos()->where('type', $module)->get();

    if (! is_null($dbQueues)) {
        d_echo(count($dbQueues) . " existing queues found in DB\n");
    }

    // Add any missing queues to the DB, keeping track of all db positions we used
    $dbKeepRows = [];
    foreach ($snmpQueues as $snmpIndex => $snmpQueue) {
        $dbQueue = $dbQueues->where('rrd_id', $snmpQueue['rrd_id'])->first();

        if (is_null($dbQueue)) {
            $dbQueue = new Qos();
            $dbQueues->push($dbQueue);
        }

        $dbQueue->device_id = $device_id;
        $dbQueue->type = $module;
        $dbQueue->title = $snmpQueue['name'];
        $dbQueue->snmp_idx = $snmpQueue['snmp_idx'];
        $dbQueue->rrd_id = $snmpQueue['rrd_id'];
        $dbQueue->egress = $egress;
        $dbQueue->ingress = $ingress;

        // Save for now
        $dbQueue->save();

        $dbKeepRows[strval($dbQueue->id)] = true;
    }

    // Set parents in a separate step just in case the parent is a QoS that we have just created
    foreach ($snmpQueues as $snmpIndex => $snmpQueue) {
        $dbQueue = $dbQueues->where('rrd_id', $snmpQueue['rrd_id'])->first();
        if (is_null($dbQueue)) {
            d_echo('Could not find a QoS entry that we just created');
            continue;
        }

        $parentPortId = null;
        $parentQosId = null;
        if (array_key_exists('parent', $snmpQueue)) {
            // Look for a matching queue first
            $parentQos = $dbQueues->where('snmp_idx', $snmpQueue['parent'])->first();

            if (! is_null($parentQos)) {
                $parentQosId = $parentQos->id;
            } else {
                $parentPort = Device::find($device_id)->ports()->where('ifIndex', $snmpQueue['parent'])->first();
                if (! is_null($parentPort)) {
                    $parentPortId = $parentPort->port_id;
                }
            }
        }

        $dbQueue->port_id = $parentPortId;
        $dbQueue->parent_id = $parentQosId;
        $dbQueue->save();
    }

    /*
    * Loop over the dbQueues array to delete unused rows
    */
    foreach ($dbQueues as $dbQueue) {
        if (! array_key_exists(strval($dbQueue->id), $dbKeepRows)) {
            echo '-';
            $dbQueue->delete();
        }
    }

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
        $snmpQueues[$qtid]['snmp_idx'] = strval($qtid);
        $snmpQueues[$qtid]['rrd_id'] = $qtname;
        $snmpQueues[$qtid]['name'] = $qtname;
        $snmpQueues[$qtid]['mark'] = $queueMarks['1.3.6.1.4.1.14988.1.1.2.2.1.3'][$qtid];
        $snmpQueues[$qtid]['parent'] = $queueParents['1.3.6.1.4.1.14988.1.1.2.2.1.4'][$qtid];

        d_echo("\nQueue Tree" . $sqid . ' name: ' . $sqname . "\n");
        d_echo('Packet Mark: ' . $queueMarks['1.3.6.1.4.1.14988.1.1.2.2.1.3'][$qtid] . "\n");
        d_echo('Parent     : ' . $queueParents['1.3.6.1.4.1.14988.1.1.2.2.1.4'][$qtid] . "\n");
    }

    syncRouterosQueues($device['device_id'], 'routeros_tree', $snmpQueues, true, false);
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
        $snmpQueues[$sqid]['snmp_idx'] = strval($sqid);
        $snmpQueues[$sqid]['rrd_id'] = $sqname;
        $snmpQueues[$sqid]['name'] = $sqname;

        d_echo("\nSimple Queue " . $sqid . ' name: ' . $sqname . "\n");
    }

    syncRouterosQueues($device['device_id'], 'routeros_simple', $snmpQueues, true, true);
}

if ($device['os'] == 'routeros') {
    discoverRouterosSimpleQueue($device);
    discoverRouterosQueueTree($device);
}
