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
    'proc3_null' => ['descr' => 'Null', 'colour' => '630606'],
    'proc3_getattr' => ['descr' => 'Get attributes', 'colour' => '50C150'],
    'proc3_setattr' => ['descr' => 'Set attributes', 'colour' => '4D65A2'],
    'proc3_lookup' => ['descr' => 'Lookup', 'colour' => '8B64A1'],
    'proc3_access' => ['descr' => 'Access', 'colour' => 'AAAA39'],
    'proc3_read' => ['descr' => 'Read', 'colour' => '308A30'],
    'proc3_write' => ['descr' => 'Write', 'colour' => '457A9A'],
    'proc3_create' => ['descr' => 'Create', 'colour' => '690D87'],
    'proc3_mkdir' => ['descr' => 'Make dir', 'colour' => '3A3478'],
    'proc3_mknod' => ['descr' => 'Make nod', 'colour' => '512E74'],
    'proc3_link' => ['descr' => 'Link', 'colour' => '072A3F'],
    'proc3_remove' => ['descr' => 'Remove', 'colour' => 'F16464'],
    'proc3_rmdir' => ['descr' => 'Remove dir', 'colour' => '57162D'],
    'proc3_rename' => ['descr' => 'Rename', 'colour' => 'A40B62'],
    'proc3_readlink' => ['descr' => 'Read link', 'colour' => '557917'],
    'proc3_readdir' => ['descr' => 'Read dir', 'colour' => 'A3C666'],
    'proc3_symlink' => ['descr' => 'Symlink', 'colour' => '85C490'],
    'proc3_readdirplus' => ['descr' => 'Read dir plus', 'colour' => 'F1F164'],
    'proc3_fsstat' => ['descr' => 'FS stat', 'colour' => 'F1F191'],
    'proc3_fsinfo' => ['descr' => 'FS info', 'colour' => '6E2770'],
    'proc3_pathconf' => ['descr' => 'Pathconf', 'colour' => '993555'],
    'proc3_commit' => ['descr' => 'Commit', 'colour' => '463176'],
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
