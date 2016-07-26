<?php

$oid = '.1.3.6.1.4.1.8072.1.3.2.4';
$nfsstats = snmp_walk($device, $oid, '-Oqv', 'NET-SNMP-EXTEND-MIB');

$name = 'nfsstat';
$app_id = $app['app_id'];
echo ' ' . $name;

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:total:GAUGE:600:0:U',
    'DS:null:GAUGE:600:0:U',
    'DS:getattr:GAUGE:600:0:U',
    'DS:setattr:GAUGE:600:0:U',
    'DS:lookup:GAUGE:600:0:U',
    'DS:access:GAUGE:600:0:U',
    'DS:read:GAUGE:600:0:U',
    'DS:write:GAUGE:600:0:U',
    'DS:create:GAUGE:600:0:U',
    'DS:mkdir:GAUGE:600:0:U',
    'DS:remove:GAUGE:600:0:U',
    'DS:rmdir:GAUGE:600:0:U',
    'DS:rename:GAUGE:600:0:U',
    'DS:readdirplus:GAUGE:600:0:U',
    'DS:fsstat:GAUGE:600:0:U',
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
