<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'os-updates';

if (! empty($agent_data['app'][$name])) {
    $osupdates = $agent_data['app'][$name];
} else {
    $options = '-Oqv';
    $mib = 'NET-SNMP-EXTEND-MIB';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.8.111.115.117.112.100.97.116.101.1';
    $osupdates = snmp_get($device, $oid, $options, $mib);
    $osupdates = preg_replace('/^.+\n/', '', $osupdates);
    $osupdates = str_replace("<<<app-os-updates>>>\n", '', $osupdates);
}

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()->addDataset('packages', 'GAUGE', 0);

$fields = ['packages' => $osupdates];

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);
update_application($app, $osupdates, $fields, $osupdates);
