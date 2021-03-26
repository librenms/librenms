<?php

require 'includes/html/graphs/common.inc.php';
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Cached seconds';
$unitlen = 15;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;
$rrd_filename = Rrd::dirFromHost($device['hostname']) . '/app-nfs-stats-' . $app['app_id'] . '.rrd';
$array = [
    'ra_size' => ['descr' => 'size', 'colour' => '091B40'],
    'ra_range01' => ['descr' => '0-10', 'colour' => '8293B3'],
    'ra_range02' => ['descr' => '10-20', 'colour' => '566B95'],
    'ra_range03' => ['descr' => '20-30', 'colour' => '1B315D'],
    'ra_range04' => ['descr' => '30-40', 'colour' => '091B40'],
    'ra_range05' => ['descr' => '40-50', 'colour' => '296F6A'],
    'ra_range06' => ['descr' => '50-60', 'colour' => '498984'],
    'ra_range07' => ['descr' => '60-70', 'colour' => '125651'],
    'ra_range08' => ['descr' => '70-80', 'colour' => '023B37'],
    'ra_range09' => ['descr' => '80-90', 'colour' => '14623A'],
    'ra_range10' => ['descr' => '90-100', 'colour' => '034423'],
    'ra_notfound' => ['descr' => 'not found', 'colour' => '590315'],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
