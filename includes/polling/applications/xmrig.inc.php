<?php

/*

LibreNMS Application for XMRig Miner

@link       https://www.upaya.net.au/
@copyright  2021 Ben Carbery
@author     Ben Carbery <yrebrac@upaya.net.au>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'xmrig';
$app_id = $app['app_id'];

echo $name;

try {
    $result = json_app_get($device, $name);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message
    log_event('application xmrig caught JsonAppException');

    return;
}
// should be doing something with error codes/messages returned in the snmp result or will they be caught above?

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('uptime', 'DERIVE', 0)
    ->addDataset('sys_nodes', 'GAUGE', 0, 256)
    ->addDataset('sys_cores', 'GAUGE', 0, 16384)
    ->addDataset('sys_threads', 'GAUGE', 0, 65536)
    ->addDataset('sys_l2', 'GAUGE', 0, 34359738368)
    ->addDataset('sys_l3', 'GAUGE', 0, 34359738368)
    ->addDataset('threads', 'GAUGE', 0, 65536)
    ->addDataset('hashes', 'DERIVE', 0)
    ->addDataset('hashrate_10s', 'GAUGE', 0, 65536000)
    ->addDataset('hashrate_60s', 'GAUGE', 0, 65536000)
    ->addDataset('hashrate_15m', 'GAUGE', 0, 65536000)
    ->addDataset('hashrate_max', 'GAUGE', 0, 65536000)
    ->addDataset('jobtime_avg', 'GAUGE', 0, 600)
    ->addDataset('shares_total', 'DERIVE', 0, 1000000000)
    ->addDataset('shares_good', 'DERIVE', 0, 1000000000)
    ->addDataset('difficulty_last', 'GAUGE', 0, 1000000000);

$fields = [
    'uptime'            => $result['data']['uptime'],
    'sys_nodes'         => $result['data']['cpu']['nodes'],
    'sys_cores'         => $result['data']['cpu']['cores'],
    'sys_threads'       => $result['data']['cpu']['threads'],
    'sys_l2'            => $result['data']['cpu']['l2'],
    'sys_l3'            => $result['data']['cpu']['l3'],
    'threads'           => count($result['data']['hashrate']['threads']),
    'hashes'            => $result['data']['results']['hashes_total'],
    'hashrate_10s'      => $result['data']['hashrate']['total'][0],
    'hashrate_60s'      => $result['data']['hashrate']['total'][1],
    'hashrate_15m'      => $result['data']['hashrate']['total'][2],
    'hashrate_max'      => $result['data']['hashrate']['highest'],
    'jobtime_avg'       => $result['data']['results']['avg_time'],
    'shares_total'      => $result['data']['results']['shares_total'],
    'shares_good'       => $result['data']['results']['shares_good'],
    'difficulty_last'   => $result['data']['results']['diff_current'],
];

/*log_event(
      "uptime: " . $result['data']['uptime']
    . ", sys_nodes: " . $result['data']['cpu']['nodes']
    . ", sys_cores: " . $result['data']['cpu']['cores']
    . ", sys_threads: " . $result['data']['cpu']['threads']
    . ", sys_l2: " . $result['data']['cpu']['l2']
    . ", sys_l3: " . $result['data']['cpu']['l3']
    . ", threads: " . count($result['data']['hashrate']['threads'])
    . ", hashes: " . $result['data']['results']['hashes_total']
    . ", hashrate_10s: " . $result['data']['hashrate']['total']['0']
    . ", hashrate_60s: " . $result['data']['hashrate']['total']['1']
    . ", hashrate_15m: " . $result['data']['hashrate']['total']['2']
    . ", hashrate_max: " . $result['data']['hashrate']['highest']
    . ", jobtime_avg: " . $result['data']['results']['avg_time'];
    . ", shares_total: " . $result['data']['results']['shares_total']
    . ", shares_good: " . $result['data']['results']['shares_good'];
    . ", shares_good: " . $result['data']['results']['shares_good'];
    . ", difficulty_last: " . $result['data']['results']['diff_current'];
);*/

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $fields);
