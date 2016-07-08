<?php

// Polls powerdns statistics from script via SNMP
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutputFull.8.112.111.119.101.114.100.110.115';

$name = 'powerdns';
$app_id = $app['app_id'];
if ($agent_data['app'][$name]) {
    $powerdns = $agent_data['app'][$name];
}
else {
    $powerdns = snmp_get($device, $oid, $options, $mib);
}

echo ' powerdns';

list ($corrupt, $def_cacheInserts, $def_cacheLookup, $latency, $pc_hit,
    $pc_miss, $pc_size, $qsize, $qc_hit, $qc_miss, $rec_answers,
    $rec_questions, $servfail, $tcp_answers, $tcp_queries, $timedout,
    $udp_answers, $udp_queries, $udp4_answers, $udp4_queries, $udp6_answers,
    $udp6_queries) = explode("\n", $powerdns);

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:corruptPackets:DERIVE:600:0:125000000000',
    'DS:def_cacheInserts:DERIVE:600:0:125000000000',
    'DS:def_cacheLookup:DERIVE:600:0:125000000000',
    'DS:latency:DERIVE:600:0:125000000000',
    'DS:pc_hit:DERIVE:600:0:125000000000',
    'DS:pc_miss:DERIVE:600:0:125000000000',
    'DS:pc_size:DERIVE:600:0:125000000000',
    'DS:qsize:DERIVE:600:0:125000000000',
    'DS:qc_hit:DERIVE:600:0:125000000000',
    'DS:qc_miss:DERIVE:600:0:125000000000',
    'DS:rec_answers:DERIVE:600:0:125000000000',
    'DS:rec_questions:DERIVE:600:0:125000000000',
    'DS:servfailPackets:DERIVE:600:0:125000000000',
    'DS:q_tcpAnswers:DERIVE:600:0:125000000000',
    'DS:q_tcpQueries:DERIVE:600:0:125000000000',
    'DS:q_timedout:DERIVE:600:0:125000000000',
    'DS:q_udpAnswers:DERIVE:600:0:125000000000',
    'DS:q_udpQueries:DERIVE:600:0:125000000000',
    'DS:q_udp4Answers:DERIVE:600:0:125000000000',
    'DS:q_udp4Queries:DERIVE:600:0:125000000000',
    'DS:q_udp6Answers:DERIVE:600:0:125000000000',
    'DS:q_udp6Queries:DERIVE:600:0:125000000000'
);

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

