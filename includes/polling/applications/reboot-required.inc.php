<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'reboot-required';
$oid = 'NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."reboot-required"';

$response = \SnmpQuery::get($oid);

if (! $response->isValid()) {
    echo PHP_EOL . $name . ': ' . $response->getErrorMessage() . PHP_EOL;

    return;
}

$raw = trim($response->value());
$reboot = ($raw === '1') ? 1 : 0;
$status = $reboot ? 'Reboot required' : 'No reboot required';

$rrd_name = ['app', $name, $app->app_id];
$rrd_def = RrdDefinition::make()->addDataset('reboot', 'GAUGE', 0, 1);

$fields = ['reboot' => $reboot];

$tags = [
    'name'     => $name,
    'app_id'   => $app->app_id,
    'rrd_def'  => $rrd_def,
    'rrd_name' => $rrd_name,
];

app('Datastore')->put($device, 'app', $tags, $fields);
update_application($app, $status, $fields, $status);
