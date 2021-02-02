<?php

/*

LibreNMS Application for XMRig Miner

@link       https://www.upaya.net.au/
@copyright  2021 Ben Carbery
@author     Ben Carbery <yrebrac@upaya.net.au>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

DESCRIPTION

This graph is not super interesting visually, but it's useful informationally,
or if you are experimenting with the optimal number of threads. You can also
set up alerts using this data source. For example, if you lose connection to
a pool and stop getting jobs, your threads will drop and you can trigger an
alert

*/


$name = 'xmrig';
$app_id = $app['app_id'];

$rrd_filename = rrd_name($device['hostname'], ['app', $name, $app_id]);

$ds_list[0]['vname'] = 'threads';
$ds_list[0]['ds'] = 'threads';
$ds_list[0]['filename'] = $rrd_filename;
$ds_list[0]['descr'] = 'XMRig Threads';
$ds_list[0]['line_width'] = 2.5;

// Monero colours
//$ds_list[0]['colour'] = 'FF6600';
//$ds_list[0]['areacolour'] = '4C4C4C';

// Lighter grey
$ds_list[0]['colour'] = 'FF6600';
$ds_list[0]['areacolour'] = '939393';
$ds_list[0]['areacolour'] = 'D1D1D1';

$ds_list[1]['vname'] = 'sys_nodes';
$ds_list[1]['ds'] = 'sys_nodes';
$ds_list[1]['filename'] = $rrd_filename;
$ds_list[1]['descr'] = 'System NUMA Nodes';

$ds_list[2]['vname'] = 'sys_cores';
$ds_list[2]['ds'] = 'sys_cores';
$ds_list[2]['filename'] = $rrd_filename;
$ds_list[2]['descr'] = 'System Cores';

$ds_list[3]['vname'] = 'sys_threads';
$ds_list[3]['ds'] = 'sys_threads';
$ds_list[3]['filename'] = $rrd_filename;
$ds_list[3]['descr'] = 'System Threads';

$ds_list[4]['vname'] = 'sys_l2';
$ds_list[4]['ds'] = 'sys_l2';
$ds_list[4]['filename'] = $rrd_filename;
$ds_list[4]['descr'] = 'System L2 Cache';
$ds_list[4]['units_text'] = 'MB';

$ds_list[5]['vname'] = 'sys_l3';
$ds_list[5]['ds'] = 'sys_l3';
$ds_list[5]['filename'] = $rrd_filename;
$ds_list[5]['descr'] = 'System L3 Cache';
$ds_list[5]['units_text'] = 'MB';

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
$float_precision = 0;

// LOCAL OPTIONS

$line_width = 1.5;
$pad_to = 18;   // padding for left-hand column in legend
$cf = 'AVERAGE';

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
    if ($vname == 'threads') {
        $rrd_options .= " COMMENT:\s"; // spacer in legend
        $rrd_options .= " COMMENT:'" . str_repeat(' ', $pad_to + 4) . "'";
        $rrd_options .= " COMMENT:'Now   '";
        $rrd_options .= " COMMENT:'Min   '";
        $rrd_options .= " COMMENT:'Max\l'";
        $rrd_options .= ' AREA:' . $vname . '#' . $areacolour;
        $rrd_options .= ' LINE' . $ds_line_width . ':' . $vname . '#' . $colour . ":'$descr'";
        $rrd_options .= ' GPRINT:' . $vname . ':AVERAGE:%4.' . $float_precision . 'lf';
        $rrd_options .= ' GPRINT:' . $vname . ':MIN:%6.' . $float_precision . 'lf';
        $rrd_options .= ' GPRINT:' . $vname . ':MAX:%6.' . $float_precision . 'lf';
        $rrd_options .= " COMMENT:'" . $units_text . "\l'";
        $rrd_options .= " COMMENT:\s"; // spacer in legend
    } elseif ($vname == 'sys_l2' || $vname == 'sys_l3') {
        //$rrd_options .= " CDEF:" . $vname . "_last=" . $vname . ",LAST";
        $rrd_options .= ' CDEF:' . $vname . '_mb=' . $vname . ',1048576,/';
        $rrd_options .= " COMMENT:'" . $descr . "'";
        $rrd_options .= ' GPRINT:' . $vname . "_mb:LAST:'%6." . $float_precision . 'lf ' . $units_text . "\l'";
    } else {
        $rrd_options .= " COMMENT:'" . $descr . "'";
        $rrd_options .= ' GPRINT:' . $vname . ":LAST:'%6." . $float_precision . 'lf ' . $units_text . "\l'";
    }
}
