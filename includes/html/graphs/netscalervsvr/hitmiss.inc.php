<?php

$oids = [
    'TotMiss',
    'TotHits',
];

$i = 0;

foreach ($oids as $oid) {
    $oid_ds = substr($oid, 0, 19);
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $oid;
    $rrd_list[$i]['ds'] = $oid_ds;
    $i++;
}

$colours = 'mixed';
$nototal = 1;
$unit_text = '';
$simple_rrd = 1;

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
