<?php
$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/xirrus-rssi.rrd";
$radios = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorIfaceName', array(), 'XIRRUS-MIB');
$rssi = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorAverageRSSI', array(), 'XIRRUS-MIB');
$dataRate = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorAverageDataRate', array(), 'XIRRUS-MIB');
$noiseFloor = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorNoiseFloor', array(), 'XIRRUS-MIB');
$associations=array();

foreach($radios as $idx => $radio) {

    $radioName = $radio['realtimeMonitorIfaceName'];
    $associations[$radioName]=0; 
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/xirrus_stats-$radioName.rrd";
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:rssi:GAUGE:600:-150:0 DS:dataRate:GAUGE:600:0:1400 DS:noiseFloor:GAUGE:600:-150:0".$config['rrd_rra']);
    }
    rrdtool_update($rrd_filename, array(
                                    'rssi'=>$rssi[$idx]['realtimeMonitorAverageRSSI'],
                                    'dataRate'=>$dataRate[$idx]['realtimeMonitorAverageDataRate'],
                                    'noiseFloor'=>$noiseFloor[$idx]['realtimeMonitorNoiseFloor']
                                  ));

}
// cleanup
unset($rrd_filename); unset($radios); unset($rssi); unset($radioName);

// station associations
// custom RRDs and graph as each AP may have 16 radios
$assoc = snmpwalk_cache_oid($device, 'XIRRUS-MIB::stationAssociationIAP', array(), 'XIRRUS-MIB');
foreach($assoc as $s) {
    $radio = array_pop($s);
    $associations[$radio]++;
}
unset($radio); unset($assoc);
// write to rrds
print_r($associations);
foreach($associations as $radio => $count) {
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/xirrus_users-$radio.rrd";
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:stations:GAUGE:600:0:3200".$config['rrd_rra']);
    }
    rrdtool_update($rrd_filename, array('stations'=>$count));	
}
// cleanup
unset($assocations); unset($rrd_filename);

$graphs['xirrus_rssi'] = TRUE;
$graphs['xirrus_dataRates'] = TRUE;
$graphs['xirrus_noiseFloor'] = TRUE;
$graphs['xirrus_stations'] = TRUE;
