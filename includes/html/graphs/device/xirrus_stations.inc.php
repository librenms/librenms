<?php

require 'includes/html/graphs/common.inc.php';

$pallette = [
    1  => '001080',
    2  => '043D85',
    3  => '096C8A',
    4  => '0F8F84',
    5  => '159461',
    6  => '1B9A3E',
    7  => '279F22',
    8  => '56A429',
    9  => '83A930',
    10 => 'AEAE38',
    11 => 'B48E40',
    12 => 'B97049',
    13 => 'BE5552',
    14 => 'C35B79',
    15 => 'C864A1',
    16 => 'CE6FC7',
];

$rrd_options .= ' -l 0 -E ';
$rrd_options .= " COMMENT:'Associated Stations    Cur     Min    Max\\n'";
$radioId = 1;
foreach (glob(Rrd::name($device['hostname'], 'xirrus_users-', '*.rrd')) as $rrd) {
    // get radio name
    preg_match('/xirrus_users-iap([0-9]{1,2}).rrd/', $rrd, $out);
    [,$radioId] = $out;

    // build graph
    $color = $pallette[$radioId];

    $descr = "iap$radioId             ";

    $rrd_options .= " DEF:stations$radioId=$rrd:stations:AVERAGE";
    $rrd_options .= " AREA:stations$radioId#" . $color . ":'" . $descr . "':STACK";
    $rrd_options .= " GPRINT:stations$radioId:LAST:'%5.0lf'";
    $rrd_options .= " GPRINT:stations$radioId:MIN:'%5.0lf'";
    $rrd_options .= " GPRINT:stations$radioId:MAX:'%5.0lf'\\l";

    $radioId++;
}//end foreach
