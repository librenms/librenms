<?php

// Polls powerdns statistics from script via SNMP

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'powerdns';
$app_id = $app['app_id'];
$powerdns = [];
echo " $name";

// unused metrics:
// deferred-packetcache-inserts, deferred-packetcache-lookup, dnsupdate-answers, dnsupdate-changes, dnsupdate-queries, dnsupdate-refused, incoming-notifications
// query-cache-size, overload-drops, rd-queries, recursion-unanswered, security-status, signatures, tcp-answers-bytes, tcp4-answers, tcp4-answers-bytes, tcp4-queries
// tcp6-answers, tcp6-answers-bytes, tcp6-queries, udp-answers-bytes, udp-do-queries, udp4-answers-bytes, udp6-answers-bytes, fd-usage, key-cache-size
// meta-cache-size, real-memory-usage, signature-cache-size, sys-msec, udp-in-errors, udp-noport-errors, udp-recvbuf-errors, udp-sndbuf-errors, uptime, user-msec

$powerdns_metrics = [
    'corruptPackets' => 'corrupt-packets',
    'def_cacheInserts' => 'deferred-cache-inserts',
    'def_cacheLookup' => 'deferred-cache-lookup',
    'latency' => 'latency',
    'pc_hit' => 'packetcache-hit',
    'pc_miss' => 'packetcache-miss',
    'pc_size' => 'packetcache-size',
    'qsize' => 'qsize-q',
    'qc_hit' => 'query-cache-hit',
    'qc_miss' => 'query-cache-miss',
    'rec_answers' => 'recursing-answers',
    'rec_questions' => 'recursing-questions',
    'servfailPackets' => 'servfail-packets',
    'q_tcpAnswers' => 'tcp-answers',
    'q_tcpQueries' => 'tcp-queries',
    'q_timedout' => 'timedout-packets',
    'q_udpAnswers' => 'udp-answers',
    'q_udpQueries' => 'udp-queries',
    'q_udp4Answers' => 'udp4-answers',
    'q_udp4Queries' => 'udp4-queries',
    'q_udp6Answers' => 'udp6-answers',
    'q_udp6Queries' => 'udp6-queries',
];

if (isset($agent_data) && isset($agent_data['app'][$name])) {
    $legacy = $agent_data['app'][$name];
} else {
    try {
        $powerdns = json_app_get($device, $name)['data'];
    } catch (JsonAppParsingFailedException $e) {
        $legacy = $e->getOutput();
    } catch (JsonAppException $e) {
        echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
        update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

        return;
    }
}

if (isset($legacy)) {
    // Legacy script, build compatible array
    [
        $powerdns['corrupt-packets'],
        $powerdns['deferred-cache-inserts'],
        $powerdns['deferred-cache-lookup'],
        $powerdns['latency'],
        $powerdns['packetcache-hit'],
        $powerdns['packetcache-miss'],
        $powerdns['packetcache-size'],
        $powerdns['qsize-q'],
        $powerdns['query-cache-hit'],
        $powerdns['query-cache-miss'],
        $powerdns['recursing-answers'],
        $powerdns['recursing-questions'],
        $powerdns['servfail-packets'],
        $powerdns['tcp-answers'],
        $powerdns['tcp-queries'],
        $powerdns['timedout-packets'],
        $powerdns['udp-answers'],
        $powerdns['udp-queries'],
        $powerdns['udp4-answers'],
        $powerdns['udp4-queries'],
        $powerdns['udp6-answers'],
        $powerdns['udp6-queries'],
        ] = explode("\n", $legacy);
}

d_echo($powerdns);

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make();
$fields = [];
foreach ($powerdns_metrics as $ds => $metric) {
    $rrd_def->addDataset($ds, 'DERIVE', 0, 125000000000);
    $fields[$ds] = isset($powerdns[$metric]) ? $powerdns[$metric] : 'U';
}

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, json_encode($powerdns), $fields);
