<?php

require 'includes/graphs/common.inc.php';

if ($width > '500') {
    $descr_len = 24;
}
else {
    $descr_len  = 12;
    $descr_len += round(($width - 250) / 8);
}

if ($nototal) {
    $descr_len += '2';
    $unitlen  += '2';
}

if ($width > '500') {
    $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5))."Now      Min      Max     Avg\l'";
    if (!$nototal) {
        $rrd_options .= " COMMENT:'Total      '";
    }

    $rrd_options .= " COMMENT:'\l'";
}
else {
    $rrd_options .= " COMMENT:'".substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5))."Now      Min      Max     Avg\l'";
}

$i    = 0;
$iter = 0;

foreach ($rrd_list as $rrd) {
    if (!$config['graph_colours'][$colours][$iter]) {
        $iter = 0;
    }

    $colour = $config['graph_colours'][$colours][$iter];

    $ds       = $rrd['ds'];
    $filename = $rrd['filename'];

    $descr = rrdtool_escape($rrd['descr'], $descr_len);

    $id = 'ds'.$i;

    $rrd_options .= ' DEF:'.$id."=$filename:$ds:AVERAGE";

    if ($simple_rrd) {
        $rrd_options .= ' CDEF:'.$id.'min='.$id.' ';
        $rrd_options .= ' CDEF:'.$id.'max='.$id.' ';
    }
    else {
        $rrd_options .= ' DEF:'.$id."min=$filename:$ds:MIN";
        $rrd_options .= ' DEF:'.$id."max=$filename:$ds:MAX";
    }

    if ($rrd['invert']) {
        $rrd_options  .= ' CDEF:'.$id.'i='.$id.',-1,*';
        $rrd_optionsb .= ' LINE1.25:'.$id.'i#'.$colour.":'$descr'";
        if (!empty($rrd['areacolour'])) {
            $rrd_optionsb .= ' AREA:'.$id.'i#'.$rrd['areacolour'];
        }
    }
    else {
        $rrd_optionsb .= ' LINE1.25:'.$id.'#'.$colour.":'$descr'";
        if (!empty($rrd['areacolour'])) {
            $rrd_optionsb .= ' AREA:'.$id.'#'.$rrd['areacolour'];
        }
    }

    $rrd_optionsb .= ' GPRINT:'.$id.':LAST:%5.2lf%s GPRINT:'.$id.'min:MIN:%5.2lf%s';
    $rrd_optionsb .= ' GPRINT:'.$id.'max:MAX:%5.2lf%s GPRINT:'.$id.":AVERAGE:'%5.2lf%s\\n'";

    $i++;
    $iter++;
}//end foreach

$rrd_options .= $rrd_optionsb;
$rrd_options .= ' HRULE:0#555555';
