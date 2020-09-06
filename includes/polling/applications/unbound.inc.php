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
    ->addDataset('a', 'DERIVE', 0, 125000000000)
    ->addDataset('ns', 'DERIVE', 0, 125000000000)
    ->addDataset('cname', 'DERIVE', 0, 125000000000)
    ->addDataset('soa', 'DERIVE', 0, 125000000000)
    ->addDataset('null', 'DERIVE', 0, 125000000000)
    ->addDataset('wks', 'DERIVE', 0, 125000000000)
    ->addDataset('ptr', 'DERIVE', 0, 125000000000)
    ->addDataset('hinfo', 'DERIVE', 0, 125000000000)
    ->addDataset('mx', 'DERIVE', 0, 125000000000)
    ->addDataset('txt', 'DERIVE', 0, 125000000000)
    ->addDataset('aaaa', 'DERIVE', 0, 125000000000)
    ->addDataset('srv', 'DERIVE', 0, 125000000000)
    ->addDataset('naptr', 'DERIVE', 0, 125000000000)
    ->addDataset('ds', 'DERIVE', 0, 125000000000)
    ->addDataset('rrsig', 'DERIVE', 0, 125000000000)
    ->addDataset('dnskey', 'DERIVE', 0, 125000000000)
    ->addDataset('tlsa', 'DERIVE', 0, 125000000000)
    ->addDataset('spf', 'DERIVE', 0, 125000000000)
    ->addDataset('axfr', 'DERIVE', 0, 125000000000)
    ->addDataset('any', 'DERIVE', 0, 125000000000)
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
    'hinfo' => $unbound['num.query.type.HINFO'],
    'mx' => $unbound['num.query.type.MX'],
    'txt' => $unbound['num.query.type.TXT'],
    'aaaa' => $unbound['num.query.type.AAAA'],
    'srv' => $unbound['num.query.type.SRV'],
    'naptr' => $unbound['num.query.type.NAPTR'],
    'ds' => $unbound['num.query.type.DS'],
    'rrsig' => $unbound['num.query.type.RRSIG'],
    'dnskey' => $unbound['num.query.type.DNSKEY'],
    'tlsa' => $unbound['num.query.type.TLSA'],
    'spf' => $unbound['num.query.type.SPF'],
    'axfr' => $unbound['num.query.type.AXFR'],
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

#Unbound requestlist
$rrd_name =  array('app', $name,'requestlist',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('max', 'DERIVE', 0, 125000000000)
    ->addDataset('overwritten', 'DERIVE', 0, 125000000000)
    ->addDataset('exceeded', 'DERIVE', 0, 125000000000);
$fields = array (
    'max' => $unbound['total.requestlist.max'],
    'overwritten' => $unbound['total.requestlist.overwritten'],
    'exceeded' => $unbound['total.requestlist.exceeded']
    );
$metrics['requestlist'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

#Unbound recursiontime
$rrd_name =  array('app', $name,'recursiontime',$app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('avg', 'GAUGE', 0, 125000000000)
    ->addDataset('median', 'GAUGE', 0, 125000000000);
$fields = array (
    'avg' => $unbound['total.recursion.time.avg'],
    'median' => $unbound['total.recursion.time.median']
    );
$metrics['recursiontime'] = $fields;
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

update_application($app, $rawdata, $metrics);

unset($lines, $unbound, $rrd_name, $rrd_def, $fields, $tags);
