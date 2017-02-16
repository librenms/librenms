<?php
$name = 'fail2ban';
$app_id = $app['app_id'];

$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutputFull.8.102.97.105.108.50.98.97.110';
$f2b = snmp_walk($device, $oid, $options, $mib);

list($banned) = explode("\n", $f2b);

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:banned:GAUGE:600:0:U',
);

$fields = array(
    'banned' =>$f2b
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
