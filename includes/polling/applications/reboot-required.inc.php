<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'reboot-required';
$options = '-Oqv';
$mib = 'NET-SNMP-EXTEND-MIB';
$oid = 'nsExtendOutput1Line."reboot-required"';

$reboot = snmp_get($device, $oid, $options, $mib);
$reboot = trim($reboot);

// Sanitize to 0 or 1
$reboot = ($reboot === '1') ? 1 : 0;
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
