<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

// $transmitPower = snmp_get($device, "transmitPower.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
// if (is_numeric($transmitPower)) {
//     $rrd_def = RrdDefinition::make()->addDataset('transmitPower', 'GAUGE', 0, 100);
//     $fields = array(
//         'transmitPower' => $transmitPower / 10,
//     );
//     $tags = compact('rrd_def');
//     data_update($device, 'cambium-650-transmitPower', $tags, $fields);
//     $os->enableGraph('cambium_650_transmitPower');
// }

// $rawReceivePower = snmp_get($device, "rawReceivePower.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
// if (is_numeric($rawReceivePower)) {
//     $rrd_def = RrdDefinition::make()->addDataset('rawReceivePower', 'GAUGE', -100, 0);
//     $fields = array(
//         'rawReceivePower' => $rawReceivePower / 10,
//     );
//     $tags = compact('rrd_def');
//     data_update($device, 'cambium-650-rawReceivePower', $tags, $fields);
//     $os->enableGraph('cambium_650_rawReceivePower');
// }

$txModulation = snmp_get($device, '.1.3.6.1.4.1.17713.7.12.15.0', '-Ovqn', '');
$rxModulation = snmp_get($device, '.1.3.6.1.4.1.17713.7.12.14.0', '-Ovqn', '');
if (is_numeric($txModulation) && is_numeric($rxModulation)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('txModulation', 'GAUGE', 0, 24)
        ->addDataset('rxModulation', 'GAUGE', 0, 24);
    $fields = [
        'txModuation' => $txModulation,
        'rxModulation' => $rxModulation,
    ];
    $tags = compact('rrd_def');
    data_update($device, 'cambium-650-modulationMode', $tags, $fields);
    $os->enableGraph('cambium_650_modulationMode');
}

// $receiveDataRate = snmp_get($device, "receiveDataRate.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
// $transmitDataRate = snmp_get($device, "transmitDataRate.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
// $aggregateDataRate = snmp_get($device, "aggregateDataRate.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
// if (is_numeric($receiveDataRate) && is_numeric($transmitDataRate) && is_numeric($aggregateDataRate)) {
//     $rrd_def = RrdDefinition::make()
//         ->addDataset('receiveDataRate', 'GAUGE', 0, 10000)
//         ->addDataset('transmitDataRate', 'GAUGE', 0, 10000)
//         ->addDataset('aggregateDataRate', 'GAUGE', 0, 10000);
//     $fields = array(
//         'receiveDataRate' => $receiveDataRate / 100,
//         'transmitDataRate' => $transmitDataRate / 100,
//         'aggregateDataRate' => $aggregateDataRate / 100,
//     );
//     $tags = compact('rrd_def');
//     data_update($device, 'cambium-650-dataRate', $tags, $fields);
//     $os->enableGraph('cambium_650_dataRate');
// }

$ssr = snmp_get($device, 'signalStrengthRatio.0', '-Ovqn', 'CAMBIUM-PTP650-MIB');
if (is_numeric($ssr)) {
    $rrd_def = RrdDefinition::make()->addDataset('ssr', 'GAUGE', -150, 150);
    $fields = [
        'ssr' => $ssr,
    ];
    $tags = compact('rrd_def');
    data_update($device, 'cambium-650-ssr', $tags, $fields);
    $os->enableGraph('cambium_650_ssr');
}

// $gps = snmp_get($device, "tDDSynchronizationStatus.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
// if ($gps == 'locked') {
//         $gps = 0;
// } elseif ($gps == 'holdover') {
//         $gps = 1;
// } elseif ($gps == 'holdoverNoGPSSyncIn') {
//     $gps = 2;
// } elseif ($gps == 'notSynchronized') {
//     $gps = 3;
// } elseif ($gps == 'notSynchronizedNoGPSSyncIn') {
//     $gps = 4;
// } elseif ($gps == 'pTPSYNCNotConnected') {
//     $gps = 5;
// } elseif ($gps == 'initialising') {
//     $gps = 6;
// } elseif ($gps == 'clusterTimingMaster') {
//     $gps = 7;
// } elseif ($gps == 'acquiringLock') {
//     $gps = 8;
// } elseif ($gps == 'inactive') {
//     $gps = 9;
// }
// if (is_numeric($gps)) {
//     $rrd_def = RrdDefinition::make()->addDataset('gps', 'GAUGE', 0, 10);
//     $fields = array(
//     'gps' => $gps,
//         );
//         $tags = compact('rrd_def');
//         data_update($device, 'cambium-650-gps', $tags, $fields);
//             $os->enableGraph('cambium_650_gps');
// }
