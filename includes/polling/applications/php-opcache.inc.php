<?php

// Polls Apache statistics from script via SNMP
use LibreNMS\RRD\RrdDefinition;

$name = 'php-opcache';
$app_id = $app['app_id'];

    $options = '-Oqv';
    $oid     = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.112.104.112.111.112.99.97.99.104.101';
    $phpopc  = snmp_get($device, $oid, $options);

echo ' php-opcache';

list($mem_free,$mem_used,$mem_waisted,$mem_int_free,$mem_int_used,$key_free,$key_used,
     $scrip_used,$hits,$hits_miss,$hits_blacklist) = explode("\n", $phpopc);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('mf', 'GAUGE', 0) // mf+mu+mw = 100%
    ->addDataset('mu', 'GAUGE', 0)
    ->addDataset('mw', 'GAUGE', 0)
    ->addDataset('if', 'GAUGE', 0) // if+iu = 100%
    ->addDataset('iu', 'GAUGE', 0)
    ->addDataset('kf', 'GAUGE', 0)
    ->addDataset('ku', 'GAUGE', 0)
    ->addDataset('kw', 'GAUGE', 0) // = ku - su
    ->addDataset('su', 'GAUGE', 0)
    ->addDataset('hu', 'GAUGE', 0) // 100%
    ->addDataset('hm', 'GAUGE', 0)
    ->addDataset('hb', 'GAUGE', 0);

$fields = array(
    'mf' => ($mem_free/1024/1024),
    'mu' => ($mem_used/1024/1024),
    'mw' => ($mem_waisted/1024/1024),
    'if' => ($mem_int_free/1024/1024),
    'iu' => ($mem_int_used/1024/1024),
    'kf' => ($key_free-$key_used),
    'ku' => $key_used,
    'kw' => ($key_used-$scrip_used),
    'su' => $scrip_used,
    'hu' => $hits,
    'hm' => $hits_miss,
    'hb' => $hits_blacklist
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $phpopc, $fields);
