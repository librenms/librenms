<?php
$radios = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorIfaceName', array(), 'XIRRUS-MIB');
$rssi = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorAverageRSSI', array(), 'XIRRUS-MIB');
$dataRate = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorAverageDataRate', array(), 'XIRRUS-MIB');
$noiseFloor = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorNoiseFloor', array(), 'XIRRUS-MIB');
$associations=array();

foreach($radios as $idx => $radio) {
    $radioName = $radio['realtimeMonitorIfaceName'];
    $associations[$radioName]=0;

    $measurement = 'xirrus_stats';
    $rrd_name = array($measurement, $radioName);
    $rrd_def = array(
        'DS:rssi:GAUGE:600:-150:0',
        'DS:dataRate:GAUGE:600:0:1400',
        'DS:noiseFloor:GAUGE:600:-150:0'
    );
    $fields = array(
        'rssi' => $rssi[$idx]['realtimeMonitorAverageRSSI'],
        'dataRate' => $dataRate[$idx]['realtimeMonitorAverageDataRate'],
        'noiseFloor' => $noiseFloor[$idx]['realtimeMonitorNoiseFloor']
    );
    $tags = compact('radioName', 'rrd_name', 'rrd_def');
    data_update($device, $measurement, $tags, $fields);
}

// if this config flag is true, don't poll for stations
// this in case of large APs which may have many stations
// to prevent causing long polling times
if ($config['xirrus_disable_stations']!=true) {
    // station associations
    // custom RRDs and graph as each AP may have 16 radios
    $assoc = snmpwalk_cache_oid($device, 'XIRRUS-MIB::stationAssociationIAP', array(), 'XIRRUS-MIB');
    foreach($assoc as $s) {
        $radio = array_pop($s);
        $associations[$radio]++;
    }
    unset($radio); unset($assoc);
    // write to rrds
    foreach($associations as $radio => $count) {
        $measurement = 'xirrus_users';
        $rrd_name = array($measurement, $radio);
        $rrd_def = 'DS:stations:GAUGE:600:0:3200';
        $fields = array(
            'stations' => $count
        );
        $tags = compact('radio', 'rrd_name', 'rrd_def');
        data_update($device, $measurement, $tags, $fields);
    }
    $graphs['xirrus_stations'] = TRUE;
} else {
    $graphs['xirrus_stations'] = FALSE;
}

$graphs['xirrus_rssi'] = TRUE;
$graphs['xirrus_dataRates'] = TRUE;
$graphs['xirrus_noiseFloor'] = TRUE;
$graphs['xirrus_stations'] = TRUE;

// cleanup
unset($rrd_def, $radios, $rssi, $radioName, $associations, $tags, $fields, $measurement);