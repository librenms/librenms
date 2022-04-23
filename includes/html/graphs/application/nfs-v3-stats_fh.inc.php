<?php

require 'includes/html/graphs/common.inc.php';
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Operations';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;
$rrd_filename = Rrd::dirFromHost($device['hostname']) . '/app-nfs-stats-' . $app['app_id'] . '.rrd';
$array = [
    'fh_lookup' => ['descr' => 'lookup', 'colour' => '136421'],
    'fh_anon' => ['descr' => 'anon', 'colour' => 'B2C945'],
    'fh_ncachedir' => ['descr' => 'ncachedir', 'colour' => '778D0D'],
    'fh_ncachenondir' => ['descr' => 'ncachenondir', 'colour' => '536400'],
    'fh_stale' => ['descr' => 'stale', 'colour' => '832119'],
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
