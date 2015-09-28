<?php

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-drbd-'.$app['app_instance'].'.rrd';

foreach (explode('|', $agent_data['app']['drbd'][$app['app_instance']]) as $part) {
    list($stat, $val) = explode('=', $part);
    if (!empty($stat)) {
        $drbd[$stat] = $val;
    }
}

if (!is_file($rrd_filename)) {
    rrdtool_create(
        $rrd_filename,
        '--step 300 
        DS:ns:DERIVE:600:0:125000000000 
        DS:nr:DERIVE:600:0:125000000000 
        DS:dw:DERIVE:600:0:125000000000 
        DS:dr:DERIVE:600:0:125000000000 
        DS:al:DERIVE:600:0:125000000000 
        DS:bm:DERIVE:600:0:125000000000 
        DS:lo:GAUGE:600:0:125000000000 
        DS:pe:GAUGE:600:0:125000000000 
        DS:ua:GAUGE:600:0:125000000000 
        DS:ap:GAUGE:600:0:125000000000 
        DS:oos:GAUGE:600:0:125000000000 '.$config['rrd_rra']
    );
}

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

rrdtool_update($rrd_filename, $fields);

unset($drbd);
