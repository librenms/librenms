<?php

require 'includes/graphs/common.inc.php';

$pallette = array(
    1  => 'FF0000',
    2  => '0000FF',
    3  => '00FF00',
    4  => 'FF00FF',
    5  => '000000',
    6  => 'FFFF00',
    7  => 'C0C0C0',
    8  => '800000',
    9  => '808000',
    10 => '008000',
    11 => '00FFFF',
    12 => '008080',
    13 => '000080',
    14 => '800080',
    15 => 'FF69B4',
    16 => '006400'
);

$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'Average Data Rate       Cur    Min    Max\\n'";
$radioId=1;
foreach(glob($config['rrd_dir'].'/'.$device['hostname'].'/xirrus_stats-*.rrd') as $rrd) {
    // get radio name
    preg_match("/xirrus_stats-iap([0-9]{1,2}).rrd/", $rrd, $out);
    list(,$radioId)=$out;

    // build graph
    $color=$pallette[$radioId];

    $descr        = "iap$radioId             ";

    $rrd_options .= " DEF:rate$radioId=$rrd:dataRate:AVERAGE";
    $rrd_options .= " LINE2:rate$radioId#".$color.":'".$descr."'";
    $rrd_options .= " GPRINT:rate$radioId:LAST:'%5.0lf'";
    $rrd_options .= " GPRINT:rate$radioId:MIN:'%5.0lf'";
    $rrd_options .= " GPRINT:rate$radioId:MAX:'%5.0lf'\\l";

    $radioId++;
}//end foreach
