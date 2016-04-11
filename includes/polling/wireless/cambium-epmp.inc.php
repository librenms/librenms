<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/cambium-epmp-RFStatus.rrd';
$cambiumSTADLRSSI = snmp_get($device, "cambiumSTADLRSSI.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
$cambiumSTADLSNR = snmp_get($device, "cambiumSTADLSNR.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
if (is_numeric($cambiumSTADLRSSI) && is_numeric($cambiumSTADLSNR)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:cambiumSTADLRSSI:GAUGE:600:-150:0 DS:cambiumSTADLSNR:GAUGE:600:0:150".$config['rrd_rra']); 
    }
    $fields = array(
        'cambiumSTADLRSSI' => $cambiumSTADLRSSI,
        'cambiumSTADLSNR' => $cambiumSTADLSNR
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_epmp_RFStatus'] = TRUE;
}

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/cambium-epmp-gps.rrd';
$cambiumGPSNumTrackedSat = snmp_get($device, "cambiumGPSNumTrackedSat.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
$cambiumGPSNumVisibleSat = snmp_get($device, "cambiumGPSNumVisibleSat.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
if (is_numeric($cambiumGPSNumTrackedSat) && is_numeric($cambiumGPSNumVisibleSat)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:numTracked:GAUGE:600:0:100000 DS:numVisible:GAUGE:600:0:100000".$config['rrd_rra']); 
    }
    $fields = array(
        'numTracked' => $cambiumGPSNumTrackedSat,
        'numVisible' => $cambiumGPSNumVisibleSat
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_epmp_gps'] = TRUE;
}

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/cambium-epmp-modulation.rrd';
$cambiumSTAUplinkMCSMode = snmp_get($device, "cambiumSTAUplinkMCSMode.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
$cambiumSTADownlinkMCSMode = snmp_get($device, "cambiumSTADownlinkMCSMode.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
if (is_numeric($cambiumSTAUplinkMCSMode) && is_numeric($cambiumSTADownlinkMCSMode)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:uplinkMCSMode:GAUGE:600:-30:30 DS:downlinkMCSMode:GAUGE:600:-30:30".$config['rrd_rra']); 
    }
    $fields = array(
        'uplinkMCSMode' => $cambiumSTAUplinkMCSMode,
        'downlinkMCSMode' => $cambiumSTADownlinkMCSMode
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_epmp_modulation'] = TRUE;
}

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/cambium-epmp-registeredSM.rrd';
$registeredSM = snmp_get($device, "cambiumAPNumberOfConnectedSTA.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
if (is_numeric($registeredSM)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:regSM:GAUGE:600:0:10000".$config['rrd_rra']); 
    }
    $fields = array(
        'regSM' => $registeredSM,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_epmp_registeredSM'] = TRUE;
}

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/cambium-epmp-access.rrd';
$sysNetworkEntryAttempt = snmp_get($device, "sysNetworkEntryAttempt.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
$sysNetworkEntrySuccess = snmp_get($device, "sysNetworkEntrySuccess.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
$sysNetworkEntryAuthenticationFailure = snmp_get($device, "sysNetworkEntryAuthenticationFailure.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
if (is_numeric($sysNetworkEntryAttempt) && is_numeric($sysNetworkEntrySuccess) && is_numeric($sysNetworkEntryAuthenticationFailure)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:entryAttempt:GAUGE:600:0:100000 DS:entryAccess:GAUGE:600:0:100000 DS:authFailure:GAUGE:600:0:100000".$config['rrd_rra']); 
    }
    $fields = array(
        'entryAttempt' => $sysNetworkEntryAttempt,
        'entryAccess' => $sysNetworkEntrySuccess,
        'authFailure' => $sysNetworkEntryAuthenticationFailure
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_epmp_access'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-epmp-gpsSync.rrd";
$gpsSync = snmp_get($device, "cambiumEffectiveSyncSource.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
if (is_numeric($gpsSync)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:gpsSync:GAUGE:600:0:4".$config['rrd_rra']); 
    }
    $fields = array(
        'gpsSync' => $gpsSync,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_epmp_gpsSync'] = TRUE;
}

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cambium-epmp-freq.rrd";
$freq = snmp_get($device, "cambiumSTAConnectedRFFrequency.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
if (is_numeric($freq)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:freq:GAUGE:600:0:100000".$config['rrd_rra']); 
    }
    $fields = array(
        'freq' => $freq,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cambium_epmp_freq'] = TRUE;
}
