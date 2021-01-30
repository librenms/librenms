<?php

/*

LibreNMS Application for XMRig Miner

Copyright(C) 2021 Ben Carbery yrebrac@upaya.net.au

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

$name = 'xmrig';
$app_id = $app['app_id'];

$rrd_filename = rrd_name($device['hostname'], ['app', $name, $app_id]);

$ds_list[0]['vname'] = 'jobtime';
$ds_list[0]['ds'] = 'jobtime_avg';
$ds_list[0]['filename'] = $rrd_filename;
$ds_list[0]['descr'] = 'Average jobtime';
$ds_list[0]['line_width'] = 2.5;
$ds_list[0]['units_text'] = 'seconds';
// Greens
//$ds_list[0]['colour'] = '009900';
//$ds_list[0]['areacolour'] = 'D5F2D5';
// Monero colours
//$ds_list[0]['colour'] = '4C4C4C';
//$ds_list[0]['areacolour'] = 'FF6600';
// Monerish
$ds_list[0]['colour'] = 'FF6600';
$ds_list[0]['areacolour'] = 'FFEEE2';

$ds_list[1]['vname'] = 'difficulty';
$ds_list[1]['ds'] = 'difficulty_last';
$ds_list[1]['filename'] = $rrd_filename;
$ds_list[1]['descr'] = 'Last difficulty';

if ($_GET['debug']) {
    print_r($ds_list);
}

// COMMON OPTIONS

//$from = ;
//$to = ;
//$width = 200;
//$height = 100;
//$inverse = false;
//$nototal = true;
//$nodetails = false;
//$noagg = true;
//$title = '';
$scale_min = 0;
//$scale_max = 0;
//$scale_rigid = anything;
//$norigid = false;
$float_precision = 1;

// LOCAL OPTIONS

$line_width = 2.0;
$pad_to = 21;   // padding for left-hand column in legend

require 'includes/html/graphs/common.inc.php';

//if ($nototal) {
//    $pad_to += '2';
//}

$i = 0;
foreach ($ds_list as $ds_item) {
    $vname = $ds_item['vname'];
    $ds = $ds_item['ds'];
    $filename = $ds_item['filename'];
    $descr = rrdtool_escape($ds_item['descr'], $pad_to);

    // CF to use
    $use_cf_last = ['nothing', 'nothing'];

    if (in_array($vname, $use_cf_last)) {
        $cf = 'LAST';
    } else {
        $cf = 'AVERAGE';
    }

    $rrd_options .= ' DEF:' . $vname . "=$filename:$ds:" . $cf;

    // Units
    if (isset($ds_item['units_text'])) {
        $units_text = $ds_item['units_text'];
    } else {
        $units_text = '';
    }

    // Line Width
    if (isset($ds_item['line_width'])) {
        $ds_line_width = $ds_item['line_width'];
    } else {
        $ds_line_wdith = $line_width;
    }

    // Line Colour
    if (isset($ds_item['colour'])) {
        $colour = $ds_item['colour'];
    } else {
        if (! \LibreNMS\Config::get("graph_colours.$colours.$i")) {
            $i = 0;
        }
        $colour = \LibreNMS\Config::get("graph_colours.$colours.$i");
        $i++;
    }

    // Area Colour
    if (isset($ds_item['areacolour'])) {
        $areacolour = $ds_item['areacolour'];
    } else {
        $areacolour = $colour . '20';
    }

    // Graph command
    if ($vname == 'jobtime') {
        $rrd_options .= " COMMENT:\s"; // spacer in legend
        $rrd_options .= " COMMENT:'" . str_repeat(' ', $pad_to + 5) . "'";
        $rrd_options .= " COMMENT:'Seconds\l'";
        $rrd_options .= ' AREA:' . $vname . '#' . $areacolour;
        $rrd_options .= ' LINE' . $ds_line_width . ':' . $vname . '#' . $colour . ":'$descr'";
        //$rrd_options .= " LINE" . $ds_line_width . ':' . $vname . '#' . $colour . ":'$descr'";
        $rrd_options .= ' GPRINT:' . $vname . ':AVERAGE:%9.' . $float_precision . "lf\l";

        $rrd_options .= " LINE1.5:60#FF0000:dashes:'Maximum jobtime'";
        $rrd_options .= " COMMENT:'            60.0\l'";
    } elseif ($vname == 'difficulty') {
        $rrd_options .= " COMMENT:\s"; // spacer in legend
        $rrd_options .= " COMMENT:'" . $descr . "  '";
        $float_precision = 0;
        $rrd_options .= ' GPRINT:' . $vname . ":LAST:'%9." . $float_precision . 'lf ' . $units_text . "\l'";
        $float_precision = 1;
    }
}
