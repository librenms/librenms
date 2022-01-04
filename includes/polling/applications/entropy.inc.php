<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'entropy';
$app_id = $app['app_id'];
$options = '-Oqv';
$mib = 'NET-SNMP-EXTEND-MIB';
$oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.101.110.116.114.111.112.121.1';

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()->addDataset('entropy', 'GAUGE', 0);

$entropy_avail = snmp_get($device, $oid, $options, $mib);

$fields = ['entropy' => $entropy_avail];

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);
update_application($app, $entropy_avail, $fields, $entropy_avail);
