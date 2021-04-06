<?php

require 'includes/html/graphs/common.inc.php';

$colours = 'mixed';
$nototal = (($width < 224) ? 1 : 0);
$unit_text = 'Seconds';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'chronyd', $app['app_id']]);
$array = [
    'system_time'   => ['descr' => 'Clock lag'],
    'last_offset'   => ['descr' => 'Last offset'],
    'rms_offset'    => ['descr' => 'Avg offset'],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = \LibreNMS\Config::get("graph_colours.$colours.$i");
        $i++;
    }
} else {
    echo "file missing: $file";
}

require 'includes/html/graphs/generic_multi_line.inc.php';
