<?php

// Polls powerdns statistics from script via SNMP

use LibreNMS\RRD\RrdDefinition;

$options      = '-O qv';
$oid          = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.8.112.111.119.101.114.100.110.115';

$name = 'powerdns';
$app_id = $app['app_id'];
if ($agent_data['app'][$name]) {
    $powerdns = $agent_data['app'][$name];
} else {
    $powerdns = snmp_get($device, $oid, $options);
    $powerdns = trim($powerdns, '"');
}

echo ' powerdns';

list ($corrupt, $def_cacheInserts, $def_cacheLookup, $latency, $pc_hit,
    $pc_miss, $pc_size, $qsize, $qc_hit, $qc_miss, $rec_answers,
    $rec_questions, $servfail, $tcp_answers, $tcp_queries, $timedout,
    $udp_answers, $udp_queries, $udp4_answers, $udp4_queries, $udp6_answers,
    $udp6_queries) = explode("\n", $powerdns);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('corruptPackets', 'DERIVE', 0, 125000000000)
    ->addDataset('def_cacheInserts', 'DERIVE', 0, 125000000000)
    ->addDataset('def_cacheLookup', 'DERIVE', 0, 125000000000)
    ->addDataset('latency', 'DERIVE', 0, 125000000000)
    ->addDataset('pc_hit', 'DERIVE', 0, 125000000000)
    ->addDataset('pc_miss', 'DERIVE', 0, 125000000000)
    ->addDataset('pc_size', 'DERIVE', 0, 125000000000)
    ->addDataset('qsize', 'DERIVE', 0, 125000000000)
    ->addDataset('qc_hit', 'DERIVE', 0, 125000000000)
    ->addDataset('qc_miss', 'DERIVE', 0, 125000000000)
    ->addDataset('rec_answers', 'DERIVE', 0, 125000000000)
    ->addDataset('rec_questions', 'DERIVE', 0, 125000000000)
    ->addDataset('servfailPackets', 'DERIVE', 0, 125000000000)
    ->addDataset('q_tcpAnswers', 'DERIVE', 0, 125000000000)
    ->addDataset('q_tcpQueries', 'DERIVE', 0, 125000000000)
    ->addDataset('q_timedout', 'DERIVE', 0, 125000000000)
    ->addDataset('q_udpAnswers', 'DERIVE', 0, 125000000000)
    ->addDataset('q_udpQueries', 'DERIVE', 0, 125000000000)
    ->addDataset('q_udp4Answers', 'DERIVE', 0, 125000000000)
    ->addDataset('q_udp4Queries', 'DERIVE', 0, 125000000000)
    ->addDataset('q_udp6Answers', 'DERIVE', 0, 125000000000)
    ->addDataset('q_udp6Queries', 'DERIVE', 0, 125000000000);

$fields = array(
    'corruptPackets'     => $corrupt,
    'def_cacheInserts'   => $def_cacheInserts,
    'def_cacheLookup'    => $def_cacheLookup,
    'latency'            => $latency,
    'pc_hit'             => $pc_hit,
    'pc_miss'            => $pc_miss,
    'pc_size'            => $pc_size,
    'qsize'              => $qsize,
    'qc_hit'             => $qc_hit,
    'qc_miss'            => $qc_miss,
    'rec_answers'        => $rec_answers,
    'rec_questions'      => $rec_questions,
    'servfailPackets'    => $servfail,
    'q_tcpAnswers'       => $tcp_answers,
    'q_tcpQueries'       => $tcp_queries,
    'q_timedout'         => $timedout,
    'q_udpAnswers'       => $udp_answers,
    'q_udpQueries'       => $udp_queries,
    'q_udp4Answers'      => $udp4_answers,
    'q_udp4Queries'      => $udp4_queries,
    'q_udp6Answers'      => $udp6_answers,
    'q_udp6Queries'      => $udp6_queries,
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $powerdns, $fields);
