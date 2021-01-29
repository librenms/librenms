<?php

$name = 'xmrig';
$app_id = $app['app_id'];

$rrd_filename = rrd_name($device['hostname'], ['app', $name, $app_id]);

$ds_list[0]['vname'] = 'hashes';
$ds_list[0]['ds'] = 'hashes';
$ds_list[0]['filename'] = $rrd_filename;
$ds_list[0]['descr'] = 'Average hashrate';
$ds_list[0]['line_width'] = 0.5;
$ds_list[0]['units_text'] = 'hashes/sec';

// Reds
//$ds_list[0]['colour'] = 'CC0000';
//$ds_list[0]['colour'] = 'CC0000';

// Magentas
//$ds_list[0]['colour'] = 'CC00CC';
//$ds_list[0]['areacolour'] = 'FFCCFF';

// Monero colours
//$ds_list[0]['colour'] = 'FF6600';
//$ds_list[0]['areacolour'] = '4C4C4C';

// Monerish
$ds_list[0]['colour'] = 'FF6600';
$ds_list[0]['areacolour'] = 'FFEEE2';

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
$pad_to = 24;   // padding for left-hand column in legend

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
    $use_cf_last = array("nothing", "nothing");

    if (in_array($vname, $use_cf_last)) {
        $cf = "LAST";

    } else {
        $cf = "AVERAGE";
    }

    $rrd_options .= " DEF:" . $vname . "=$filename:$ds:" . $cf;

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
    if ($vname == "hashes") {
        // Convert rrdata to moving averages
        $rrd_options .= " CDEF:" . "hashes_ma_15m" . "=hashes,900,TRENDNAN";
        $rrd_options .= " CDEF:" . "hashes_ma_1h" . "=hashes,3600,TRENDNAN"; 
        $rrd_options .= " CDEF:" . "hashes_ma_1d". "=hashes,86400,TRENDNAN"; 

        $rrd_options .= " COMMENT:\s"; // spacer in legend
        $rrd_options .= " COMMENT:'" . str_repeat(' ',$pad_to + 6) . "'";
        $rrd_options .= " COMMENT:'H/s\l'";

        $rrd_options .= " AREA:" . $vname . "#" . $areacolour;
        $rrd_options .= " LINE" . $ds_line_width . ':' . $vname . '#' . $colour . ":'$descr'";
        $rrd_options .= " GPRINT:" . $vname . ":AVERAGE:%6." . $float_precision . "lf\l";

        $ds_line_width = 2.5;
        //$rrd_options .= " LINE" . $ds_line_width . ":hashes_ma_15m#" . "9999FF" . ":'Moving average 15 minutes'";
        //$rrd_options .= " GPRINT:" . "hashes_ma_15m" . ":AVERAGE:%6." . $float_precision . "lf\l";

        $rrd_options .= " LINE" . $ds_line_width . ":hashes_ma_1h#" . "6666FF" . ":'Moving average 1 hour    '";
        $rrd_options .= " GPRINT:" . "hashes_ma_1h" . ":AVERAGE:%6." . $float_precision . "lf\l";

        $rrd_options .= " LINE" . $ds_line_width . ":hashes_ma_1d#" . "000099". ":'Moving average 1 day     '";
        $rrd_options .= " GPRINT:" . "hashes_ma_1d" . ":AVERAGE:%6." . $float_precision . "lf\l";
    }
}
