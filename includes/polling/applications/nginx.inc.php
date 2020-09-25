<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'nginx';
$app_id = $app['app_id'];
if (! empty($agent_data['app'][$name])) {
    $nginx = $agent_data['app'][$name];
} else {
    // Polls nginx statistics from script via SNMP
    $nginx = snmp_get($device, '.1.3.6.1.4.1.8072.1.3.2.3.1.2.5.110.103.105.110.120', '-Ovq');
}
$nginx = trim($nginx, '"');

echo ' nginx';

[$active, $reading, $writing, $waiting, $req] = array_map('rtrim', explode("\n", $nginx));
d_echo("active: $active reading: $reading writing: $writing waiting: $waiting Requests: $req\n");

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('Requests', 'DERIVE', 0, 125000000000)
    ->addDataset('Active', 'GAUGE', 0, 125000000000)
    ->addDataset('Reading', 'GAUGE', 0, 125000000000)
    ->addDataset('Writing', 'GAUGE', 0, 125000000000)
    ->addDataset('Waiting', 'GAUGE', 0, 125000000000);

$fields = [
    'Requests' => $req,
    'Active'   => $active,
    'Reading'  => $reading,
    'Writing'  => $writing,
    'Waiting'  => $waiting,
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $nginx, $fields);

// Unset the variables we set here
unset($nginx, $active, $reading, $writing, $waiting, $req, $rrd_name, $rrd_def, $tags);
