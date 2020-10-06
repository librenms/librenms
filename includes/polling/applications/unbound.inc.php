<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'unbound';
$app_id = $app['app_id'];
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
$rrd_name = ['app', $name, 'queries', $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('type0', 'DERIVE', 0, 125000000000)
    ->addDataset('A', 'DERIVE', 0, 125000000000)
    ->addDataset('NS', 'DERIVE', 0, 125000000000)
    ->addDataset('CNAME', 'DERIVE', 0, 125000000000)
    ->addDataset('SOA', 'DERIVE', 0, 125000000000)
    ->addDataset('NULL', 'DERIVE', 0, 125000000000)
    ->addDataset('WKS', 'DERIVE', 0, 125000000000)
    ->addDataset('PTR', 'DERIVE', 0, 125000000000)
    ->addDataset('MX', 'DERIVE', 0, 125000000000)
    ->addDataset('TXT', 'DERIVE', 0, 125000000000)
    ->addDataset('AAAA', 'DERIVE', 0, 125000000000)
    ->addDataset('SRV', 'DERIVE', 0, 125000000000)
    ->addDataset('NAPTR', 'DERIVE', 0, 125000000000)
    ->addDataset('DS', 'DERIVE', 0, 125000000000)
    ->addDataset('DNSKEY', 'DERIVE', 0, 125000000000)
    ->addDataset('SPF', 'DERIVE', 0, 125000000000)
    ->addDataset('ANY', 'DERIVE', 0, 125000000000)
    ->addDataset('other', 'DERIVE', 0, 125000000000);
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
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
//Unbound Cache
$rrd_name = ['app', $name, 'cache', $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('queries', 'DERIVE', 0, 125000000000)
    ->addDataset('hits', 'DERIVE', 0, 125000000000)
    ->addDataset('misses', 'DERIVE', 0, 125000000000);
$fields = [
    'queries' => $unbound['total.num.queries'],
    'hits' => $unbound['total.num.cachehits'],
    'misses' => $unbound['total.num.cachemiss'],
];
$metrics['cache'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
//Unbound Operations - Total opcodes and three valuable return codes
$rrd_name = ['app', $name, 'operations', $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('opcodeQuery', 'DERIVE', 0, 125000000000)
    ->addDataset('rcodeNOERROR', 'DERIVE', 0, 125000000000)
    ->addDataset('rcodeNXDOMAIN', 'DERIVE', 0, 125000000000)
    ->addDataset('rcodeNodata', 'DERIVE', 0, 125000000000);
$fields = [
    'opcodeQuery' => $unbound['num.query.opcode.query'],
    'rcodeNOERROR' => $unbound['num.answer.rcode.noerror'],
    'rcodeNXDOMAIN' => $unbound['num.answer.rcode.nxdomain'],
    'rcodeNodata' => $unbound['num.answer.rcode.nodata'],
];
$metrics['operations'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

//Unbound requestlist
$rrd_name = ['app', $name, 'requestlist', $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('max', 'DERIVE', 0, 125000000000)
    ->addDataset('overwritten', 'DERIVE', 0, 125000000000)
    ->addDataset('exceeded', 'DERIVE', 0, 125000000000);
$fields = [
    'max' => $unbound['total.requestlist.max'],
    'overwritten' => $unbound['total.requestlist.overwritten'],
    'exceeded' => $unbound['total.requestlist.exceeded'],
];
$metrics['requestlist'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

//Unbound recursiontime
$rrd_name = ['app', $name, 'recursiontime', $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('avg', 'GAUGE', 0, 125000000000)
    ->addDataset('median', 'GAUGE', 0, 125000000000);
$fields = [
    'avg' => $unbound['total.recursion.time.avg'],
    'median' => $unbound['total.recursion.time.median'],
];
$metrics['recursiontime'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

update_application($app, $rawdata, $metrics);

unset($lines, $unbound, $rrd_name, $rrd_def, $fields, $tags);
