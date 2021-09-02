<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'mysql', $app['app_id']]);

$array = [
    'TOC'  => ['descr' => 'Table Cache'],
    'OFs'  => ['descr' => 'Open Files'],
    'OTs'  => ['descr' => 'Open Tables'],
    'OdTs' => ['descr' => 'Opened Tables'],
];

$i = 0;
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        // $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $file";
}

$colours = 'mixed';
$nototal = 1;
$unit_text = '';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
