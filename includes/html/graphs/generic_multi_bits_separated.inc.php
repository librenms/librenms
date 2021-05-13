<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage graphs
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

require 'includes/html/graphs/common.inc.php';

$stacked = generate_stacked_graphs();

$i = 0;
if ($width > '1500') {
    $descr_len = 40;
} elseif ($width >= '500') {
    $descr_len = 8;
    $descr_len += min(40, round(($width - 320) / 15));
} else {
    $descr_len = 8;
    $descr_len += min(20, round(($width - 260) / 9.5));
}

$unit_text = 'Bits/sec';

if (! $noagg || ! $nodetails) {
    if ($width > '500') {
        $rrd_options .= sprintf(" COMMENT:'%s'", substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)));
        $rrd_options .= sprintf(" COMMENT:'%12s'", 'Current');
        $rrd_options .= sprintf(" COMMENT:'%10s'", 'Average');
        $rrd_options .= sprintf(" COMMENT:'%10s'", 'Maximum');
        if (! $nototal) {
            $rrd_options .= sprintf(" COMMENT:'%8s'", 'Total');
        }
        $rrd_options .= " COMMENT:'\l'";
    } else {
        $nototal = true;
        $rrd_options .= sprintf(" COMMENT:'%s'", substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)));
        $rrd_options .= sprintf(" COMMENT:'%12s'", 'Now');
        $rrd_options .= sprintf(" COMMENT:'%10s'", 'Avg');
        $rrd_options .= sprintf(" COMMENT:'%10s\l'", 'Max');
    }
}

if (! isset($multiplier)) {
    $multiplier = '8';
}

foreach ($rrd_list as $rrd) {
    if (! \LibreNMS\Config::get("graph_colours.$colours_in.$iter") || ! \LibreNMS\Config::get("graph_colours.$colours_out.$iter")) {
        $iter = 0;
    }

    $colour_in = \LibreNMS\Config::get("graph_colours.$colours_in.$iter");
    $colour_out = \LibreNMS\Config::get("graph_colours.$colours_out.$iter");

    if (! $nodetails) {
        if (isset($rrd['descr_in'])) {
            $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr_in'], $descr_len) . '  In';
        } else {
            $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr'], $descr_len) . '  In';
        }
        $descr_out = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr_out'], $descr_len) . ' Out';
    }

    $rrd_options .= ' DEF:' . $in . $i . '=' . $rrd['filename'] . ':' . $ds_in . ':AVERAGE ';
    $rrd_options .= ' DEF:' . $out . $i . '=' . $rrd['filename'] . ':' . $ds_out . ':AVERAGE ';
    $rrd_options .= ' CDEF:inB' . $i . '=in' . $i . ",$multiplier,* ";
    $rrd_options .= ' CDEF:outB' . $i . '=out' . $i . ",$multiplier,*";
    $rrd_options .= ' CDEF:outB' . $i . '_neg=outB' . $i . ',' . $stacked['stacked'] . ',*';
    $rrd_options .= ' CDEF:octets' . $i . '=inB' . $i . ',outB' . $i . ',+';

    if (! $nototal) {
        $rrd_options .= ' VDEF:totin' . $i . '=inB' . $i . ',TOTAL';
        $rrd_options .= ' VDEF:totout' . $i . '=outB' . $i . ',TOTAL';
        $rrd_options .= ' VDEF:tot' . $i . '=octets' . $i . ',TOTAL';
    }

    if ($i) {
        $stack = ':STACK';
    }

    $rrd_options .= ' AREA:inB' . $i . '#' . $colour_in . $stacked['transparency'] . ":'" . $descr . "'$stack";
    if (! $nodetails) {
        $rrd_options .= ' GPRINT:inB' . $i . ':LAST:%6.' . $float_precision . "lf%s$units";
        $rrd_options .= ' GPRINT:inB' . $i . ':AVERAGE:%6.' . $float_precision . "lf%s$units";
        $rrd_options .= ' GPRINT:inB' . $i . ':MAX:%6.' . $float_precision . "lf%s$units";
        if (! $nototal) {
            $rrd_options .= ' GPRINT:totin' . $i . ':%6.' . $float_precision . "lf%s$total_units";
        }

        $rrd_options .= '\l';
    }

    $rrd_options .= " 'HRULE:0#" . $colour_out . ':' . $descr_out . "'";
    $rrd_optionsb .= " 'AREA:outB" . $i . '_neg#' . $colour_out . $stacked['transparency'] . ":$stack'";

    if (! $nodetails) {
        $rrd_options .= ' GPRINT:outB' . $i . ':LAST:%6.' . $float_precision . "lf%s$units";
        $rrd_options .= ' GPRINT:outB' . $i . ':AVERAGE:%6.' . $float_precision . "lf%s$units";
        $rrd_options .= ' GPRINT:outB' . $i . ':MAX:%6.' . $float_precision . "lf%s$units";
        if (! $nototal) {
            $rrd_options .= ' GPRINT:totout' . $i . ':%6.' . $float_precision . "lf%s$total_units";
        }

        $rrd_options .= '\l';
    }

    $rrd_options .= " 'COMMENT:\l'";

    if ($i >= 1) {
        $aggr_in .= ',';
        $aggr_out .= ',';
    }

    if ($i > 1) {
        $aggr_in .= 'ADDNAN,';
        $aggr_out .= 'ADDNAN,';
    }

    $aggr_in .= $in . $i;
    $aggr_out .= $out . $i;

    $i++;
    $iter++;
}

if (! $noagg) {
    $rrd_options .= ' CDEF:aggr' . $in . 'bytes=' . $aggr_in . ',ADDNAN';
    $rrd_options .= ' CDEF:aggr' . $out . 'bytes=' . $aggr_out . ',ADDNAN';
    $rrd_options .= ' CDEF:aggrinbits=aggrinbytes,' . $multiplier . ',*';
    $rrd_options .= ' CDEF:aggroutbits=aggroutbytes,' . $multiplier . ',*';
    $rrd_options .= ' VDEF:totalin=aggrinbytes,TOTAL';
    $rrd_options .= ' VDEF:totalout=aggroutbytes,TOTAL';
    $rrd_options .= " COMMENT:' \\n'";
    $rrd_options .= " COMMENT:'" . substr(str_pad('Aggregate', ($descr_len + 5)), 0, ($descr_len + 5)) . 'In' . "'";
    $rrd_options .= ' GPRINT:aggrinbits:LAST:%6.' . $float_precision . "lf%s$units";
    $rrd_options .= ' GPRINT:aggrinbits:AVERAGE:%6.' . $float_precision . "lf%s$units";
    $rrd_options .= ' GPRINT:aggrinbits:MAX:%6.' . $float_precision . "lf%s$units";
    if (! $nototal) {
        $rrd_options .= ' GPRINT:totalin:%6.' . $float_precision . "lf%s$total_units";
    }

    $rrd_options .= '\\n';
    $rrd_options .= " COMMENT:'" . substr(str_pad('Aggregate', ($descr_len + 4)), 0, ($descr_len + 4)) . 'Out' . "'";
    $rrd_options .= ' GPRINT:aggroutbits:LAST:%6.' . $float_precision . "lf%s$units";
    $rrd_options .= ' GPRINT:aggroutbits:AVERAGE:%6.' . $float_precision . "lf%s$units";
    $rrd_options .= ' GPRINT:aggroutbits:MAX:%6.' . $float_precision . "lf%s$units";
    if (! $nototal) {
        $rrd_options .= ' GPRINT:totalout:%6.' . $float_precision . "lf%s$total_units";
    }

    $rrd_options .= '\\n';
}

if ($custom_graph) {
    $rrd_options .= $custom_graph;
}

$rrd_options .= $rrd_optionsb;
$rrd_options .= ' HRULE:0#999999';

unset($stacked);
