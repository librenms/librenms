<?php

$name = 'certificate';
$app_id = $app['app_id'];
$colours = 'mega';
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;
$scale_min = 0;

if (isset($vars['cert_name'])) {
    $cert_name_list = [$vars['cert_name']];
} else {
    $cert_name_list = Rrd::getRrdApplicationArrays($device, $app_id, $name);
}

$int = 0;
$rrd_list = [];
while (isset($cert_name_list[$int])) {
    $cert_name = $cert_name_list[$int];
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id, $cert_name]);

    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr'    => $cert_name,
            'ds'       => $rrdVar,
        ];
    }
    $int++;
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
