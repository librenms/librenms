<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'drbd', $app['app_instance']]);

$array = [
    'lo' => 'Local I/O',
    'pe' => 'Pending',
    'ua' => 'UnAcked',
    'ap' => 'App Pending',
];

$i = 0;
if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        if (is_array($var)) {
            $rrd_list[$i]['descr'] = $var['descr'];
        } else {
            $rrd_list[$i]['descr'] = $var;
        }

        $rrd_list[$i]['ds'] = $ds;
        $i++;
    }
} else {
    echo "file missing: $file";
}

$colours = 'mixed';
$nototal = 0;
$unit_text = '';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
