<?php

// Polls MailScanner statistics from script via SNMP

use LibreNMS\RRD\RrdDefinition;

$name = 'mailscannerV2';
$options = '-Oqv';
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.11.109.97.105.108.115.99.97.110.110.101.114';

$mailscanner = snmp_get($device, $oid, $options);

[$msg_recv, $msg_rejected, $msg_relay, $msg_sent, $msg_waiting, $spam, $virus] = explode("\n", $mailscanner);

$rrd_def = RrdDefinition::make()
    ->addDataset('msg_recv', 'COUNTER', 0, 125000000000)
    ->addDataset('msg_rejected', 'COUNTER', 0, 12500000000)
    ->addDataset('msg_relay', 'COUNTER', 0, 125000000000)
    ->addDataset('msg_sent', 'COUNTER', 0, 125000000000)
    ->addDataset('msg_waiting', 'COUNTER', 0, 125000000000)
    ->addDataset('spam', 'COUNTER', 0, 125000000000)
    ->addDataset('virus', 'COUNTER', 0, 125000000000);

$fields = [
    'msg_recv' => $msg_recv,
    'msg_rejected' => $msg_rejected,
    'msg_relay' => $msg_relay,
    'msg_sent' => $msg_sent,
    'msg_waiting' => $msg_waiting,
    'spam' => $spam,
    'virus' => $virus,
];

$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'rrd_name' => ['app', $name, $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);
update_application($app, $mailscanner, $fields);
