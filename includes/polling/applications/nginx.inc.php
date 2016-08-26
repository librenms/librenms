<?php

$name = 'nginx';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name])) {
    $nginx = $agent_data['app'][$name];
}
else {
    // NET-SNMP-EXTEND-MIB::nsExtendOutputFull."nginx"
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.5.110.103.105.110.120';
    $nginx = snmp_get($device, $oid, '-Ovq');
}

echo ' nginx';

list($active, $reading, $writing, $waiting, $req) = explode("\n", $nginx);
d_echo("active: $active reading: $reading writing: $writing waiting: $waiting Requests: $req");

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:Requests:DERIVE:600:0:125000000000',
    'DS:Active:GAUGE:600:0:125000000000',
    'DS:Reading:GAUGE:600:0:125000000000',
    'DS:Writing:GAUGE:600:0:125000000000',
    'DS:Waiting:GAUGE:600:0:125000000000'
);

$fields = array(
    'Requests' => $req,
    'Active'   => $active,
    'Reading'  => $reading,
    'Writing'  => $writing,
    'Waiting'  => $waiting,
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

// Unset the variables we set here
unset($nginx, $active, $reading, $writing, $req, $rrd_name, $rrd_def, $tags);
