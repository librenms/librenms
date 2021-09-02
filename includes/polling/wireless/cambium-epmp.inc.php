<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
use LibreNMS\RRD\RrdDefinition;

// $cambiumSTADLRSSI = snmp_get($device, "cambiumSTADLRSSI.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
// $cambiumSTADLSNR = snmp_get($device, "cambiumSTADLSNR.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
// if (is_numeric($cambiumSTADLRSSI) && is_numeric($cambiumSTADLSNR)) {
//     $rrd_def = RrdDefinition::make()
//         ->addDataset('cambiumSTADLRSSI', 'GAUGE', -150, 0)
//         ->addDataset('cambiumSTADLSNR', 'GAUGE', 0, 150);
//     $fields = array(
//         'cambiumSTADLRSSI' => $cambiumSTADLRSSI,
//         'cambiumSTADLSNR' => $cambiumSTADLSNR
//     );
//     $tags = compact('rrd_def');
//     data_update($device, 'cambium-epmp-RFStatus', $tags, $fields);
//     $os->enableGraph('cambium_epmp_RFStatus');
// }

$cambiumGPSNumTrackedSat = snmp_get($device, 'cambiumGPSNumTrackedSat.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
$cambiumGPSNumVisibleSat = snmp_get($device, 'cambiumGPSNumVisibleSat.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
if (is_numeric($cambiumGPSNumTrackedSat) && is_numeric($cambiumGPSNumVisibleSat)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('numTracked', 'GAUGE', 0, 100000)
        ->addDataset('numVisible', 'GAUGE', 0, 100000);
    $fields = [
        'numTracked' => $cambiumGPSNumTrackedSat,
        'numVisible' => $cambiumGPSNumVisibleSat,
    ];
    $tags = compact('rrd_def');
    data_update($device, 'cambium-epmp-gps', $tags, $fields);
    $os->enableGraph('cambium_epmp_gps');
}

$cambiumSTAUplinkMCSMode = snmp_get($device, 'cambiumSTAUplinkMCSMode.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
$cambiumSTADownlinkMCSMode = snmp_get($device, 'cambiumSTADownlinkMCSMode.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
if (is_numeric($cambiumSTAUplinkMCSMode) && is_numeric($cambiumSTADownlinkMCSMode)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('uplinkMCSMode', 'GAUGE', -30, 30)
        ->addDataset('downlinkMCSMode', 'GAUGE', -30, 30);
    $fields = [
        'uplinkMCSMode' => $cambiumSTAUplinkMCSMode,
        'downlinkMCSMode' => $cambiumSTADownlinkMCSMode,
    ];
    $tags = compact('rrd_def');
    data_update($device, 'cambium-epmp-modulation', $tags, $fields);
    $os->enableGraph('cambium_epmp_modulation');
}

// $registeredSM = snmp_get($device, "cambiumAPNumberOfConnectedSTA.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
// if (is_numeric($registeredSM)) {
//     $rrd_def = RrdDefinition::make()->addDataset('regSM', 'GAUGE', 0, 10000);
//     $fields = array(
//         'regSM' => $registeredSM,
//     );
//     $tags = compact('rrd_def');
//     data_update($device, 'cambium-epmp-registeredSM', $tags, $fields);
//     $os->enableGraph('cambium_epmp_registeredSM');
// }

$sysNetworkEntryAttempt = snmp_get($device, 'sysNetworkEntryAttempt.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
$sysNetworkEntrySuccess = snmp_get($device, 'sysNetworkEntrySuccess.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
$sysNetworkEntryAuthenticationFailure = snmp_get($device, 'sysNetworkEntryAuthenticationFailure.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
if (is_numeric($sysNetworkEntryAttempt) && is_numeric($sysNetworkEntrySuccess) && is_numeric($sysNetworkEntryAuthenticationFailure)) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('entryAttempt', 'GAUGE', 0, 100000)
        ->addDataset('entryAccess', 'GAUGE', 0, 100000)
        ->addDataset('authFailure', 'GAUGE', 0, 100000);
    $fields = [
        'entryAttempt' => $sysNetworkEntryAttempt,
        'entryAccess' => $sysNetworkEntrySuccess,
        'authFailure' => $sysNetworkEntryAuthenticationFailure,
    ];
    $tags = compact('rrd_def');
    data_update($device, 'cambium-epmp-access', $tags, $fields);
    $os->enableGraph('cambium_epmp_access');
}

$gpsSync = snmp_get($device, 'cambiumEffectiveSyncSource.0', '-Ovqn', 'CAMBIUM-PMP80211-MIB');
if (is_numeric($gpsSync)) {
    $rrd_def = RrdDefinition::make()->addDataset('gpsSync', 'GAUGE', 0, 4);
    $fields = [
        'gpsSync' => $gpsSync,
    ];
    $tags = compact('rrd_def');
    data_update($device, 'cambium-epmp-gpsSync', $tags, $fields);
    $os->enableGraph('cambium_epmp_gpsSync');
}

// $freq = snmp_get($device, "cambiumSTAConnectedRFFrequency.0", "-Ovqn", "CAMBIUM-PMP80211-MIB");
// if (is_numeric($freq)) {
//     $rrd_def = RrdDefinition::make()->addDataset('freq', 'GAUGE', 0, 100000);
//     $fields = array(
//         'freq' => $freq,
//     );
//     $tags = compact('rrd_def');
//     data_update($device, 'cambium-epmp-freq', $tags, $fields);
//     $os->enableGraph('cambium_epmp_freq');
// }

$multi_get_array = snmp_get_multi($device, ['ulWLanTotalAvailableFrameTimePerSecond.0', 'ulWLanTotalUsedFrameTimePerSecond.0', 'dlWLanTotalAvailableFrameTimePerSecond.0', 'dlWLanTotalUsedFrameTimePerSecond.0'], '-OQU', 'CAMBIUM-PMP80211-MIB');

$ulWLanTotalAvailableFrameTimePerSecond = $multi_get_array[0]['CAMBIUM-PMP80211-MIB::ulWLanTotalAvailableFrameTimePerSecond'];
$ulWLanTotalUsedFrameTimePerSecond = $multi_get_array[0]['CAMBIUM-PMP80211-MIB::ulWLanTotalUsedFrameTimePerSecond'];
$dlWLanTotalAvailableFrameTimePerSecond = $multi_get_array[0]['CAMBIUM-PMP80211-MIB::dlWLanTotalAvailableFrameTimePerSecond'];
$dlWLanTotalUsedFrameTimePerSecond = $multi_get_array[0]['CAMBIUM-PMP80211-MIB::dlWLanTotalUsedFrameTimePerSecond'];

if (is_numeric($ulWLanTotalAvailableFrameTimePerSecond) && is_numeric($ulWLanTotalUsedFrameTimePerSecond) && is_numeric($ulWLanTotalAvailableFrameTimePerSecond) && is_numeric($ulWLanTotalUsedFrameTimePerSecond)) {
    $ulWlanFrameUtilization = round((($ulWLanTotalUsedFrameTimePerSecond / $ulWLanTotalAvailableFrameTimePerSecond) * 100), 2);
    $dlWlanFrameUtilization = round((($dlWLanTotalUsedFrameTimePerSecond / $dlWLanTotalAvailableFrameTimePerSecond) * 100), 2);
    d_echo($dlWlanFrameUtilization);
    d_echo($ulWlanFrameUtilization);
    $rrd_def = RrdDefinition::make()
            ->addDataset('ulwlanfrut', 'GAUGE', 0, 100000)
            ->addDataset('dlwlanfrut', 'GAUGE', 0, 100000);
    $fields = [
        'ulwlanframeutilization' => $ulWlanFrameUtilization,
        'dlwlanframeutilization' => $dlWlanFrameUtilization,
    ];
    $tags = compact('rrd_def');
    data_update($device, 'cambium-epmp-frameUtilization', $tags, $fields);
    $os->enableGraph('cambium-epmp-frameUtilization');
}
unset($multi_get_array, $ulWlanFrameUtilization, $ulWLanTotalAvailableFrameTimePerSecond, $ulWLanTotalUsedFrameTimePerSecond, $dlWlanFrameUtilization, $dlWLanTotalAvailableFrameTimePerSecond, $dlWLanTotalUsedFrameTimePerSecond);
