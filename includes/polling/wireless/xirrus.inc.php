<?php
$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/xirrus-rssi.rrd";
$radios = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorIfaceName', array(), 'XIRRUS-MIB');
$rssi = snmpwalk_cache_oid($device, 'XIRRUS-MIB::realtimeMonitorAverageRSSI', array(), 'XIRRUS-MIB');
foreach($radios as $idx => $radio) {

    $radioName = $radio['realtimeMonitorIfaceName'];
    $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/xirrus_rssi-$idx.rrd";
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:rssi:GAUGE:600:-150:0".$config['rrd_rra']);
    }
    rrdtool_update($rrd_filename, array('rssi'=>$rssi[$idx]['realtimeMonitorAverageRSSI']));

}
// cleanup
unset($rrd_filename); unset($radios); unset($rssi); unset($radioName);
$graphs['xirrus_rssi'] = TRUE;
?>
