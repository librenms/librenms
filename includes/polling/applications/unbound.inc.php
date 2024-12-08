<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'unbound';

if (! empty($agent_data['app'][$name])) {
    $rawdata = $agent_data['app'][$name];
} else {
    $options = '-Oqv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.7.117.110.98.111.117.110.100';
    $rawdata = snmp_get($device, $oid, $options);
}
//Format Data
$lines = explode("\n", $rawdata);
$unbound = [];
$metrics = [];
foreach ($lines as $line) {
    [$var,$value] = explode('=', $line);
    $unbound[strtolower($var)] = $value;
}
//Unbound Queries
$rrd_def = RrdDefinition::make()
    ->addDataset('type0', 'GAUGE', 0, 125000000000)
    ->addDataset('A', 'GAUGE', 0, 125000000000)
    ->addDataset('NS', 'GAUGE', 0, 125000000000)
    ->addDataset('CNAME', 'GAUGE', 0, 125000000000)
    ->addDataset('SOA', 'GAUGE', 0, 125000000000)
    ->addDataset('NULL', 'GAUGE', 0, 125000000000)
    ->addDataset('WKS', 'GAUGE', 0, 125000000000)
    ->addDataset('PTR', 'GAUGE', 0, 125000000000)
    ->addDataset('MX', 'GAUGE', 0, 125000000000)
    ->addDataset('TXT', 'GAUGE', 0, 125000000000)
    ->addDataset('AAAA', 'GAUGE', 0, 125000000000)
    ->addDataset('SRV', 'GAUGE', 0, 125000000000)
    ->addDataset('NAPTR', 'GAUGE', 0, 125000000000)
    ->addDataset('DS', 'GAUGE', 0, 125000000000)
    ->addDataset('DNSKEY', 'GAUGE', 0, 125000000000)
    ->addDataset('SPF', 'GAUGE', 0, 125000000000)
    ->addDataset('ANY', 'GAUGE', 0, 125000000000)
    ->addDataset('other', 'GAUGE', 0, 125000000000);
$fields = [
    'type0' => $unbound['num.query.type.type0'],
    'A' => $unbound['num.query.type.a'],
    'NS' => $unbound['num.query.type.ns'],
    'CNAME' => $unbound['num.query.type.cname'],
    'SOA' => $unbound['num.query.type.soa'],
    'NULL' => $unbound['num.query.type.null'],
    'WKS' => $unbound['num.query.type.wks'],
    'PTR' => $unbound['num.query.type.ptr'],
    'MX' => $unbound['num.query.type.mx'],
    'TXT' => $unbound['num.query.type.txt'],
    'AAAA' => $unbound['num.query.type.aaaa'],
    'SRV' => $unbound['num.query.type.src'],
    'NAPTR' => $unbound['num.query.type.naptr'],
    'DS' => $unbound['num.query.type.ds'],
    'DNSKEY' => $unbound['num.query.type.dnskey'],
    'SPF' => $unbound['num.query.type.spf'],
    'ANY' => $unbound['num.query.type.any'],
    'other' => $unbound['num.query.type.other'],
];
$metrics['queries'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'queries',
    'rrd_name' => ['app', $name, 'queries', $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);
//Unbound Cache
$rrd_def = RrdDefinition::make()
    ->addDataset('queries', 'GAUGE', 0, 125000000000)
    ->addDataset('hits', 'GAUGE', 0, 125000000000)
    ->addDataset('misses', 'GAUGE', 0, 125000000000);
$fields = [
    'queries' => $unbound['total.num.queries'],
    'hits' => $unbound['total.num.cachehits'],
    'misses' => $unbound['total.num.cachemiss'],
];
$metrics['cache'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'cache',
    'rrd_name' => ['app', $name, 'cache', $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);
//Unbound Operations - Total opcodes and three valuable return codes
$rrd_def = RrdDefinition::make()
    ->addDataset('opcodeQuery', 'GAUGE', 0, 125000000000)
    ->addDataset('rcodeNOERROR', 'GAUGE', 0, 125000000000)
    ->addDataset('rcodeNXDOMAIN', 'GAUGE', 0, 125000000000)
    ->addDataset('rcodeNodata', 'GAUGE', 0, 125000000000);
$fields = [
    'opcodeQuery' => $unbound['num.query.opcode.query'],
    'rcodeNOERROR' => $unbound['num.answer.rcode.noerror'],
    'rcodeNXDOMAIN' => $unbound['num.answer.rcode.nxdomain'],
    'rcodeNodata' => $unbound['num.answer.rcode.nodata'],
];
$metrics['operations'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'operations',
    'rrd_name' => ['app', $name, 'operations', $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);

//Unbound requestlist
$rrd_def = RrdDefinition::make()
    ->addDataset('max', 'GAUGE', 0, 125000000000)
    ->addDataset('overwritten', 'GAUGE', 0, 125000000000)
    ->addDataset('exceeded', 'GAUGE', 0, 125000000000);
$fields = [
    'max' => $unbound['total.requestlist.max'],
    'overwritten' => $unbound['total.requestlist.overwritten'],
    'exceeded' => $unbound['total.requestlist.exceeded'],
];
$metrics['requestlist'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'requestlist',
    'rrd_name' => ['app', $name, 'requestlist', $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);

//Unbound recursiontime
$rrd_def = RrdDefinition::make()
    ->addDataset('avg', 'GAUGE', 0, 125000000000)
    ->addDataset('median', 'GAUGE', 0, 125000000000);
$fields = [
    'avg' => $unbound['total.recursion.time.avg'],
    'median' => $unbound['total.recursion.time.median'],
];
$metrics['recursiontime'] = $fields;
$tags = [
    'name' => $name,
    'app_id' => $app->app_id,
    'type' => 'recursiontime',
    'rrd_name' => ['app', $name, 'recursiontime', $app->app_id],
    'rrd_def' => $rrd_def,
];
data_update($device, 'app', $tags, $fields);

update_application($app, $rawdata, $metrics);

unset($lines, $unbound, $rrd_def, $fields, $tags);
