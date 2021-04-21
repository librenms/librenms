<?php

require 'includes/html/graphs/common.inc.php';
$descr_len = 16;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'mysql', $app['app_id']]);

$array = [
    'IBILog' => 'AIO Log',
    'IBISc'  => 'AIO Sync',
    'IBIFLg' => 'Buf Pool Flush',
    'IBFBl'  => 'Log Flushes',
    'IBIIAo' => 'Insert Buf AIO Read',
    'IBIAd'  => 'Normal AIO Read',
    'IBIAe'  => 'Normal AIO Writes',
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
$nototal = 1;
$unit_text = '';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
