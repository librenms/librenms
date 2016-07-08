<?php

require 'includes/graphs/common.inc.php';

$colours      = 'mixed';
$nototal      = (($width < 224) ? 1 : 0);
$unit_text    = 'Milliseconds';
$rrd_filename = rrd_name($device['hostname'], array('app', 'ntpclient', $app['app_id']));
$array        = array(
                 'offset'    => array('descr' => 'Offset'),
                 'jitter'    => array('descr' => 'Jitter'),
                 'noise'     => array('descr' => 'Noise'),
                 'stability' => array('descr' => 'Stability'),
                );

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $vars) {
        $rrd_list[$i]['filename']   = $rrd_filename;
        $rrd_list[$i]['descr']  = $vars['descr'];
        $rrd_list[$i]['ds']     = $ds;
        $rrd_list[$i]['colour'] = $config['graph_colours'][$colours][$i];
        $i++;
    }
}
else {
    echo "file missing: $file";
}

require 'includes/graphs/generic_multi_line.inc.php';
