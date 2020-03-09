<?php

// Polls backupninja statistics from script via SNMP
use LibreNMS\RRD\RrdDefinition;

$name = 'backupninja';
$app_id = $app['app_id'];

if (!empty($agent_data['app'][$name])) {
    $backupninja = $agent_data['app'][$name];
} else {
    $options = '-Oqv';
    $oid     = '.1.3.6.1.4.1.2021.220.3.1.1.11.98.97.99.107.117.112.110.105.110.106.97';
    $backupninja  = snmp_get($device, $oid, $options);
}

echo ' backupninja';

$datas = json_decode($backupninja);
$last_actions = $datas->actions;
$last_fatal = $datas->fatal;
$last_error = $datas->error;
$last_warning = $datas->warning;

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('last_actions', 'GAUGE', 0)
    ->addDataset('last_fatal', 'GAUGE', 0)
    ->addDataset('last_error', 'GAUGE', 0)
    ->addDataset('last_warning', 'GAUGE', 0);

$fields = array(
                'last_actions'   => intval(trim($last_actions)),
                'last_fatal'     => intval(trim($last_fatal)),
                'last_error'     => intval(trim($last_error)),
                'last_warning'   => intval(trim($last_warning)),
);

// Debug
d_echo("backupninja : $fields");

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $backupninja, $fields);
