<?php

## Polls powerdns statistics from script via SNMP

$rrd_filename  = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-powerdns-".$app['app_id'].".rrd";
$options       = "-O qv";
$oid           = "nsExtendOutputFull.8.112.111.119.101.114.100.110.115";

$powerdns      = snmp_get($device, $oid, $options);

echo(" powerdns");

list ($corrupt, $def_cacheInserts, $def_cacheLookup, $latency, $pc_hit, $pc_miss, $pc_size, $qsize, $qc_hit,
      $qc_miss, $rec_answers, $rec_questions, $servfail, $tcp_answers, $tcp_queries, $timedout,
      $udp_answers, $udp_queries, $udp4_answers, $udp4_queries, $udp6_answers, $udp6_queries) = explode("\n", $powerdns);

if (!is_file($rrd_filename))
{
  rrdtool_create($rrd_filename, "--step 300 \
        DS:corruptPackets:DERIVE:600:0:125000000000 \
        DS:def_cacheInserts:DERIVE:600:0:125000000000 \
        DS:def_cacheLookup:DERIVE:600:0:125000000000 \
        DS:latency:DERIVE:600:0:125000000000 \
        DS:pc_hit:DERIVE:600:0:125000000000 \
        DS:pc_miss:DERIVE:600:0:125000000000 \
        DS:pc_size:DERIVE:600:0:125000000000 \
        DS:qsize:DERIVE:600:0:125000000000 \
        DS:qc_hit:DERIVE:600:0:125000000000 \
        DS:qc_miss:DERIVE:600:0:125000000000 \
        DS:rec_answers:DERIVE:600:0:125000000000 \
        DS:rec_questions:DERIVE:600:0:125000000000 \
        DS:servfailPackets:DERIVE:600:0:125000000000 \
        DS:q_tcpAnswers:DERIVE:600:0:125000000000 \
        DS:q_tcpQueries:DERIVE:600:0:125000000000 \
        DS:q_timedout:DERIVE:600:0:125000000000 \
        DS:q_udpAnswers:DERIVE:600:0:125000000000 \
        DS:q_udpQueries:DERIVE:600:0:125000000000 \
        DS:q_udp4Answers:DERIVE:600:0:125000000000 \
        DS:q_udp4Queries:DERIVE:600:0:125000000000 \
        DS:q_udp6Answers:DERIVE:600:0:125000000000 \
        DS:q_udp6Queries:DERIVE:600:0:125000000000 ".$config['rrd_rra']);
}

rrdtool_update($rrd_filename,  "N:$corrupt:$def_cacheInserts:$def_cacheLookup:$latency:$pc_hit:$pc_miss:$pc_size:$qsize:$qc_hit:$qc_miss:$rec_answers:$rec_questions:$servfail:$tcp_answers:$tcp_queries:$timedout:$udp_answers:$udp_queries:$udp4_answers:$udp4_queries:$udp6_answers:$udp6_queries");

?>
