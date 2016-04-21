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

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-250-transmitPower.rrd";
$transmitPower = snmp_get($device, "transmitPower.0", "-Ovqn", "CAMBIUM-PTP250-MIB");
if (is_numeric($transmitPower)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:transmitPower:GAUGE:600:0:100".$config['rrd_rra']); 
    }
    $transmitPower = $transmitPower / 10;
    $fields = array(
        'transmitPower' => $transmitPower,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_250_transmitPower'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-250-receivePower.rrd";
$receivePower = snmp_get($device, "receivePower.0", "-Ovqn", "CAMBIUM-PTP250-MIB");
$noiseFloor = snmp_get($device, "noiseFloor.0", "-Ovqn", "CAMBIUM-PTP250-MIB");
if (is_numeric($receivePower)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:receivePower:GAUGE:600:-150:0 DS:noiseFloor:GAUGE:600:-150:0".$config['rrd_rra']); 
    }
    $receivePower = $receivePower / 10;
    $fields = array(
        'receivePower' => $receivePower,
        'noiseFloor' => $noiseFloor,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_250_receivePower'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-250-modulationMode.rrd";
$txModulation = snmp_get($device, ".1.3.6.1.4.1.17713.250.5.9.0", "-Ovqn", "");
$rxModulation = snmp_get($device, ".1.3.6.1.4.1.17713.250.5.8.0", "-Ovqn", "");
if (is_numeric($txModulation) && is_numeric($rxModulation)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:txModulation:GAUGE:600:0:24 DS:rxModulation:GAUGE:600:0:24".$config['rrd_rra']); 
    }
    $fields = array(
        'txModuation' => $txModulation,
        'rxModulation' => $rxModulation,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_250_modulationMode'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-250-dataRate.rrd";
$receiveDataRate = snmp_get($device, "receiveDataRate.0", "-Ovqn", "CAMBIUM-PTP250-MIB");
$transmitDataRate = snmp_get($device, "transmitDataRate.0", "-Ovqn", "CAMBIUM-PTP250-MIB");
$aggregateDataRate = snmp_get($device, "aggregateDataRate.0", "-Ovqn", "CAMBIUM-PTP250-MIB");
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
    $graphs['cambium_250_dataRate'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-250-ssr.rrd";
$ssr = snmp_get($device, "signalStrengthRatio.0", "-Ovqn", "CAMBIUM-PTP250-MIB");
if (is_numeric($ssr)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:ssr:GAUGE:600:-150:150".$config['rrd_rra']); 
    }
    $fields = array(
        'ssr' => $ssr,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_250_ssr'] = TRUE;
}