<?php

$name = 'mojo_cape_submit';
$app_id = $app['app_id'];
$unit_text = 'Bytes';
$colours = 'rainbow';
$dostack = 0;
$printtotal = 1;
$addarea = 0;
$transparency = 0;
$float_precision = 3;

$slugs_all = $app->data['slugs'] ?? [];

$slugs = array_slice(array_keys($slugs_all), 0, 12);

$rrd_list = [];
foreach ($slugs as $index => $slug) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'slugs___-___' . $slug]);
    $rrd_list[] = [
        'filename' => $rrd_filename,
        'descr' => $slug,
        'ds' => 'sub_count',
    ];
}

if (count($rrd_list)) {
    d_echo('No relevant log file RRDs found');
}

require 'includes/html/graphs/generic_multi_line.inc.php';
