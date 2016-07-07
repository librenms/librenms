<?php


$name = 'drbd';
$app_instance = $app['app_instance'];
$app_id = $app['app_id'];
foreach (explode('|', $agent_data['app'][$name][$app_instance]) as $part) {
    list($stat, $val) = explode('=', $part);
    if (!empty($stat)) {
        $drbd[$stat] = $val;
    }
}

$rrd_name = array('app', $name, $app_instance);
$rrd_def = array(
    'DS:ns:DERIVE:600:0:125000000000',
    'DS:nr:DERIVE:600:0:125000000000',
    'DS:dw:DERIVE:600:0:125000000000',
    'DS:dr:DERIVE:600:0:125000000000',
    'DS:al:DERIVE:600:0:125000000000',
    'DS:bm:DERIVE:600:0:125000000000',
    'DS:lo:GAUGE:600:0:125000000000',
    'DS:pe:GAUGE:600:0:125000000000',
    'DS:ua:GAUGE:600:0:125000000000',
    'DS:ap:GAUGE:600:0:125000000000',
    'DS:oos:GAUGE:600:0:125000000000'
);


$fields = array(
    'ns'  => $drbd['ns'],
    'nr'  => $drbd['nr'],
    'dw'  => $drbd['dw'],
    'dr'  => $drbd['dr'],
    'al'  => $drbd['al'],
    'bm'  => $drbd['bm'],
    'lo'  => $drbd['lo'],
    'pe'  => $drbd['pe'],
    'ua'  => $drbd['ua'],
    'ap'  => $drbd['ap'],
    'oos' => $drbd['oos'],
);

$tags = array('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

unset($drbd);
