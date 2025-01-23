<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'rebootrequired';

$options = '-Oqv';
$mib = 'NET-SNMP-EXTEND-MIB';
$oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.14.114.101.98.111.111.116.114.101.113.117.105.114.101.100.1';

$rebootrequired = snmp_get($device, $oid, $options, $mib);
$rebootrequired = preg_replace('/^.+\n/', '', $rebootrequired);

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()->addDataset('state', 'GAUGE', 0);

$fields = ['state' => $rebootrequired];

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);
update_application($app, $rebootrequired, $fields, $rebootrequired);
