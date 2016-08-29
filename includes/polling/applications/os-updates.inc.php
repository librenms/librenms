<?php
$name = 'os-updates';
$app_id = $app['app_id'];
$options = '-O qv';
$mib = 'NET-SNMP-EXTEND-MIB';
$oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.8.111.115.117.112.100.97.116.101.1';

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:packages:GAUGE:600:0:U',
);

$osupdates = snmp_get($device, $oid, $options, $mib);

$fields = array('packages' => $osupdates,);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
