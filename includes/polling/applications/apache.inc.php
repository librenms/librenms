<?php

// Polls Apache statistics from script via SNMP
$name = 'apache';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name])) {
    $apache = $agent_data['app'][$name];
} else {
    $options = '-O qv';
    $oid     = 'nsExtendOutputFull.6.97.112.97.99.104.101';
    $apache  = snmp_get($device, $oid, $options);
}

echo ' apache';

list ($total_access, $total_kbyte, $cpuload, $uptime, $reqpersec, $bytespersec,
    $bytesperreq, $busyworkers, $idleworkers, $score_wait, $score_start,
    $score_reading, $score_writing, $score_keepalive, $score_dns,
    $score_closing, $score_logging, $score_graceful, $score_idle, $score_open) = explode("\n", $apache);

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:access:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:kbyte:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:cpu:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:uptime:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:reqpersec:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:bytespersec:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:byesperreq:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:busyworkers:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:idleworkers:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_wait:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_start:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_reading:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_writing:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_keepalive:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_dns:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_closing:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_logging:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_graceful:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_idle:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:sb_open:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000'
);

$fields = array(
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
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
