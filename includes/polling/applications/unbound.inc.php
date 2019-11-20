<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'unbound';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name])) {
    $rawdata = $agent_data['app'][$name];
} else {
    $options = '-Oqv';
    $oid     = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.7.117.110.98.111.117.110.100';
    $rawdata  = snmp_get($device, $oid, $options);
}
#Format Data
$lines = explode("\n", $rawdata);
$unbound = array();
$metrics = array();
foreach ($lines as $line) {
    list($var,$value) = explode('=', $line);
    $unbound[$var] = $value;
}
#Unbound Queries
$rrd_name =  array('app', $name,'queries',$app_id);
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
$fields = array (
    'type0' => $unbound['num.query.type.TYPE0'],
    'a' => $unbound['num.query.type.A'],
    'ns' => $unbound['num.query.type.NS'],
    'cname' => $unbound['num.query.type.CNAME'],
    'soa' => $unbound['num.query.type.SOA'],
    'null' => $unbound['num.query.type.NULL'],
    'wks' => $unbound['num.query.type.WKS'],
    'ptr' => $unbound['num.query.type.PTR'],
    'mx' => $unbound['num.query.type.MX'],
    'txt' => $unbound['num.query.type.TXT'],
    'aaaa' => $unbound['num.query.type.AAAA'],
    'srv' => $unbound['num.query.type.SRV'],
    'naptr' => $unbound['num.query.type.NAPTR'],
    'ds' => $unbound['num.query.type.DS'],
    'dnskey' => $unbound['num.query.type.DNSKEY'],
    'spf' => $unbound['num.query.type.SPF'],
    'any' => $unbound['num.query.type.ANY'],
    'other' => $unbound['num.query.type.other']
    );
$metrics['queries'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
#Unbound Cache
$rrd_name =  array('app', $name,'cache',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('queries', 'DERIVE', 0, 125000000000)
    ->addDataset('hits', 'DERIVE', 0, 125000000000)
    ->addDataset('misses', 'DERIVE', 0, 125000000000);
$fields = array (
    'queries' => $unbound['total.num.queries'],
    'hits' => $unbound['total.num.cachehits'],
    'misses' => $unbound['total.num.cachemiss']
    );
$metrics['cache'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
#Unbound Operations - Total opcodes and three valuable return codes
$rrd_name =  array('app', $name,'operations',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('opcodeQuery', 'DERIVE', 0, 125000000000)
    ->addDataset('rcodeNOERROR', 'DERIVE', 0, 125000000000)
    ->addDataset('rcodeNXDOMAIN', 'DERIVE', 0, 125000000000)
    ->addDataset('rcodeNodata', 'DERIVE', 0, 125000000000);
$fields = array (
    'opcodeQuery' => $unbound['num.query.opcode.QUERY'],
    'rcodeNOERROR' => $unbound['num.answer.rcode.NOERROR'],
    'rcodeNXDOMAIN' => $unbound['num.answer.rcode.NXDOMAIN'],
    'rcodeNodata' => $unbound['num.answer.rcode.nodata']
    );
$metrics['operations'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
 
update_application($app, $rawdata, $metrics);

unset($lines, $unbound, $rrd_name, $rrd_def, $fields, $tags);
