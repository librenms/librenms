<?php
$name = 'unbound';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name])) {
    $rawdata = $agent_data['app'][$name];
}else{
	echo "Unbound Missing";
	return;
}
#Format Data
$lines = explode("\n",$rawdata);
$unbound = array();
foreach ($lines as $line) {
	list($var,$value) = explode('=',$line);
	$unbound[$var] = $value;
}
#Unbound Queries
$rrd_name =  array('app', $name,'queries',$app_id);
$rrd_def = array(
	'DS:type0:DERIVE:600:0:125000000000',
	'DS:A:DERIVE:600:0:125000000000',
	'DS:NS:DERIVE:600:0:125000000000',
	'DS:CNAME:DERIVE:600:0:125000000000',
	'DS:SOA:DERIVE:600:0:125000000000',
	'DS:NULL:DERIVE:600:0:125000000000',
	'DS:WKS:DERIVE:600:0:125000000000',
	'DS:PTR:DERIVE:600:0:125000000000',
	'DS:MX:DERIVE:600:0:125000000000',
	'DS:TXT:DERIVE:600:0:125000000000',
	'DS:AAAA:DERIVE:600:0:125000000000',
	'DS:SRV:DERIVE:600:0:125000000000',
	'DS:NAPTR:DERIVE:600:0:125000000000',
	'DS:DS:DERIVE:600:0:125000000000',
	'DS:DNSKEY:DERIVE:600:0:125000000000',
	'DS:SPF:DERIVE:600:0:125000000000',
	'DS:ANY:DERIVE:600:0:125000000000',
	'DS:other:DERIVE:600:0:125000000000'
	);
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
$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
unset($lines , $unbound, $rrd_name, $rrd_def, $fields, $tags);