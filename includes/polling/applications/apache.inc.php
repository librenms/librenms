<?php

// Polls Apache statistics from script via SNMP
use LibreNMS\RRD\RrdDefinition;

$name = 'apache';
$app_id = $app['app_id'];
if (! empty($agent_data['app'][$name])) {
    $apache = $agent_data['app'][$name];
} else {
    $options = '-Oqv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.6.97.112.97.99.104.101';
    $apache = snmp_get($device, $oid, $options);
}

echo ' apache';

[$total_access, $total_kbyte, $cpuload, $uptime, $reqpersec, $bytespersec,
    $bytesperreq, $busyworkers, $idleworkers, $score_wait, $score_start,
    $score_reading, $score_writing, $score_keepalive, $score_dns,
    $score_closing, $score_logging, $score_graceful, $score_idle, $score_open] = explode("\n", $apache);

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('access', 'DERIVE', 0, 125000000000)
    ->addDataset('kbyte', 'DERIVE', 0, 125000000000)
    ->addDataset('cpu', 'GAUGE', 0, 125000000000)
    ->addDataset('uptime', 'GAUGE', 0, 125000000000)
    ->addDataset('reqpersec', 'GAUGE', 0, 125000000000)
    ->addDataset('bytespersec', 'GAUGE', 0, 125000000000)
    ->addDataset('byesperreq', 'GAUGE', 0, 125000000000)
    ->addDataset('busyworkers', 'GAUGE', 0, 125000000000)
    ->addDataset('idleworkers', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_wait', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_start', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_reading', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_writing', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_keepalive', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_dns', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_closing', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_logging', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_graceful', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_idle', 'GAUGE', 0, 125000000000)
    ->addDataset('sb_open', 'GAUGE', 0, 125000000000);

$fields = [
    'access'       => intval(trim($total_access, '"')),
    'kbyte'        => $total_kbyte,
    'cpu'          => $cpuload,
    'uptime'       => $uptime,
    'reqpersec'    => $reqpersec,
    'bytespersec'  => $bytespersec,
    'byesperreq'   => $bytesperreq,
    'busyworkers'  => $busyworkers,
    'idleworkers'  => $idleworkers,
    'sb_wait'      => $score_wait,
    'sb_start'     => $score_start,
    'sb_reading'   => $score_reading,
    'sb_writing'   => $score_writing,
    'sb_keepalive' => $score_keepalive,
    'sb_dns'       => $score_dns,
    'sb_closing'   => $score_closing,
    'sb_logging'   => $score_logging,
    'sb_graceful'  => $score_graceful,
    'sb_idle'      => $score_idle,
    'sb_open'      => intval(trim($score_open, '"')),
];

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $apache, $fields);
