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

$ds_list[0]['vname'] = 'shares_good';
$ds_list[0]['ds'] = 'shares_good';
$ds_list[0]['filename'] = $rrd_filename;
$ds_list[0]['descr'] = 'Accepted';

$ds_list[1]['vname'] = 'shares_total';
$ds_list[1]['ds'] = 'shares_total';
$ds_list[1]['filename'] = $rrd_filename;
$ds_list[1]['descr'] = 'Submitted';
$ds_list[1]['colour'] = '4C4C4C'; // Monero gray


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
$scale_pm = 0;
//$scale_max = 0;
//$scale_rigid = anything;
//$norigid = false;
$float_precision = 2;

// LOCAL OPTIONS

$line_width = 1;
$pad_to = 16;   // padding for left-hand column in legend

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
    if ($vname == 'shares_good') {
        // Legend Header
        $rrd_options .= " COMMENT:\s"; // spacer in legend
        $rrd_options .= " COMMENT:'" . str_repeat(' ', $pad_to - 1) . "'";
        $rrd_options .= " COMMENT:'Shares/min\l'";

        // Accepted
        $rrd_options .= " CDEF:shares_good_pm=shares_good,60,*"; // convert to per minute
        $vname = "shares_good_pm";
        $areacolour = 'D5F2D5'; // Green 1
        $areacolour = 'BCF0BC'; // Green 2
        $rrd_options .= " AREA:" . $vname . "#" . $areacolour . ":'$descr'";
        $rrd_options .= " GPRINT:" . $vname . ":AVERAGE:'%6." . $float_precision . "lf\l'";
    }

    if ($vname == 'shares_total') {
        // Rejected
        $rrd_options .= " CDEF:shares_bad=shares_total,shares_good,-";
        $rrd_options .= " CDEF:shares_bad_pm=shares_bad,60,*"; // convert to per minute
        $vname = "shares_bad_pm";
        $bad_descr = rrdtool_escape("Rejected", $pad_to);
        $areacolour = 'FF6666'; // Red
        $rrd_options .= " AREA:" . $vname . "#" . $areacolour . ":'$bad_descr'" . ":STACK";
        $rrd_options .= " GPRINT:" . $vname . ":AVERAGE:'%6." . $float_precision . "lf\l'";

        // Submitted
        $rrd_options .= " CDEF:shares_total_pm=shares_total,60,*"; // convert to per minute
        $vname = "shares_total_pm";
        $rrd_options .= " LINE" . $ds_line_width . ":" . $vname . "#" . $colour . ":'$descr'";
        $rrd_options .= " GPRINT:" . $vname . ":AVERAGE:'%6." . $float_precision . "lf\l'";
    }

}

