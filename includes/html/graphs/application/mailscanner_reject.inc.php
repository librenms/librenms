<?php

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'mixed';
$nototal = (($width < 550) ? 1 : 0);
$unit_text = 'Messages/sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'mailscannerV2', $app['app_id']]);
$array = [
    'msg_rejected' => ['descr' => 'Rejected'],
    'msg_relay'    => ['descr' => 'Relayed'],
    'msg_waiting'  => ['descr' => 'Waiting'],
];

$i = 0;
$x = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    $max_colours = count(Config::get("graph_colours.$colours"));
    foreach ($array as $ds => $var) {
        $x = (($x <= $max_colours) ? $x : 0);
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = \LibreNMS\Config::get("graph_colours.$colours.$x");
        $i++;
        $x++;
    }
}

require 'includes/html/graphs/generic_multi_line.inc.php';
