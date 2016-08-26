<?php
$name = 'os-updates';
$app_id = $app['app_id'];

// NET-SNMP-EXTEND-MIB::nsExtendOutLine."osupdate".1
$oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.8.111.115.117.112.100.97.116.101.1';
$osupdates = snmp_get($device, $oid, $options, $mib);

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:packages:GAUGE:600:0:U',
);

$fields = array('packages' => $osupdates,);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
