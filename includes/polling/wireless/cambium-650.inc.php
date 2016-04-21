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

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-650-transmitPower.rrd";
$transmitPower = snmp_get($device, "transmitPower.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
if (is_numeric($transmitPower)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:transmitPower:GAUGE:600:0:100".$config['rrd_rra']); 
    }
    $transmitPower = $transmitPower / 10;
    $fields = array(
        'transmitPower' => $transmitPower,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_650_transmitPower'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-650-rawReceivePower.rrd";
$rawReceivePower = snmp_get($device, "rawReceivePower.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
if (is_numeric($rawReceivePower)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:rawReceivePower:GAUGE:600:-100:0".$config['rrd_rra']); 
    }
    $rawReceivePower = $rawReceivePower / 10;
    $fields = array(
        'rawReceivePower' => $rawReceivePower,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_650_rawReceivePower'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-650-modulationMode.rrd";
$txModulation = snmp_get($device, ".1.3.6.1.4.1.17713.7.12.15.0", "-Ovqn", "");
$rxModulation = snmp_get($device, ".1.3.6.1.4.1.17713.7.12.14.0", "-Ovqn", "");
if (is_numeric($txModulation) && is_numeric($rxModulation)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:txModulation:GAUGE:600:0:24 DS:rxModulation:GAUGE:600:0:24".$config['rrd_rra']); 
    }   
    $fields = array(
        'txModuation' => $txModulation,
        'rxModulation' => $rxModulation,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_650_modulationMode'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-650-dataRate.rrd";
$receiveDataRate = snmp_get($device, "receiveDataRate.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
$transmitDataRate = snmp_get($device, "transmitDataRate.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
$aggregateDataRate = snmp_get($device, "aggregateDataRate.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
if (is_numeric($receiveDataRate) && is_numeric($transmitDataRate) && is_numeric($aggregateDataRate)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:receiveDataRate:GAUGE:600:0:10000 DS:transmitDataRate:GAUGE:600:0:10000 DS:aggregateDataRate:GAUGE:600:0:10000".$config['rrd_rra']); 
    }
    $receiveDataRate = $receiveDataRate / 100;
    $transmitDataRate = $transmitDataRate / 100;
    $aggregateDataRate = $aggregateDataRate / 100;
    $fields = array(
        'receiveDataRate' => $receiveDataRate,
        'transmitDataRate' => $transmitDataRate,
        'aggregateDataRate' => $aggregateDataRate,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_650_dataRate'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-650-ssr.rrd";
$ssr = snmp_get($device, "signalStrengthRatio.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
if (is_numeric($ssr)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:ssr:GAUGE:600:-150:150".$config['rrd_rra']); 
    }
    $fields = array(
        'ssr' => $ssr,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_650_ssr'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-650-gps.rrd";
$gps = snmp_get($device, "tDDSynchronizationStatus.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
if ($gps == 'locked') {
        $gps = 0;
    }
    else if ($gps == 'holdover') {
        $gps = 1;
    }
    else if ($gps == 'holdoverNoGPSSyncIn') {
        $gps = 2;
    }
    else if ($gps == 'notSynchronized') {
        $gps = 3;
    }
    else if ($gps == 'notSynchronizedNoGPSSyncIn') {
        $gps = 4;
    }
    else if ($gps == 'pTPSYNCNotConnected') {
        $gps = 5;
    }
    else if ($gps == 'initialising') {
        $gps = 6;
    }
    else if ($gps == 'clusterTimingMaster') {
        $gps = 7;
    }
    else if ($gps == 'acquiringLock') {
        $gps = 8;
    }
    else if ($gps == 'inactive') {
        $gps = 9;
    }
if (is_numeric($gps)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:gps:GAUGE:600:0:10".$config['rrd_rra']); 
    }
    $fields = array(
        'gps' => $gps,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_650_gps'] = TRUE;
}