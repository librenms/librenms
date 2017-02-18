<?php
$name = 'nfsstat';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.4';

echo ' ' . $name;

$nfsstats = snmp_walk($device, $oid, '-Oqv', 'NET-SNMP-EXTEND-MIB');

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:total:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:null:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:getattr:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:setattr:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:lookup:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:access:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:read:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:write:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:create:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:mkdir:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:remove:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:rmdir:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:rename:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:readdirplus:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
    'DS:fsstat:GAUGE:'.$config['rrd']['heartbeat'].':0:U',
);

$data = explode("\n", $nfsstats);
$fields = array(
    'total' => $data[0],
    'null' => $data[1],
    'getattr' => $data[2],
    'setattr' => $data[3],
    'lookup' => $data[4],
    'access' => $data[5],
    'read' => $data[6],
    'write' => $data[7],
    'create' => $data[8],
    'mkdir' => $data[9],
    'remove' => $data[10],
    'rmdir' => $data[11],
    'rename' => $data[12],
    'readdirplus' => $data[13],
    'fsstat' => $data[14],
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

unset($nfsstats, $rrd_name, $rrd_def, $data, $fields, $tags);
