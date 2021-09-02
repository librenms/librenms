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
if ($width > '500') {
    $descr_len = 18;
} else {
    $descr_len = 8;
    $descr_len += round(($width - 260) / 9.5);
}

$unit_text = 'Bits/sec';

if ($width > '500') {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)) . "    Current      Average     Maximum      '";
    if (! $nototal) {
        $rrd_options .= " COMMENT:'Total      '";
    }

    $rrd_options .= " COMMENT:'\l'";
} else {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)) . "     Now         Ave          Max\l'";
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

    if (isset($rrd['descr_in'])) {
        $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr_in'], $descr_len) . '  In';
    } else {
        $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr'], $descr_len) . '  In';
    }

    $descr_out = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr_out'], $descr_len) . ' Out';

    $rrd_options .= ' DEF:' . $in . $i . '=' . $rrd['filename'] . ':' . $ds_in . ':AVERAGE ';
    $rrd_options .= ' DEF:' . $out . $i . '=' . $rrd['filename'] . ':' . $ds_out . ':AVERAGE ';
    $rrd_options .= ' CDEF:inB' . $i . '=in' . $i . ",$multiplier,* ";
    $rrd_options .= ' CDEF:outB' . $i . '=out' . $i . ",$multiplier,*";
    $rrd_options .= ' CDEF:outB' . $i . '_neg=outB' . $i . ',' . $stacked['stacked'] . ',*';
    $rrd_options .= ' CDEF:octets' . $i . '=inB' . $i . ',outB' . $i . ',+';

    if (! $args['nototal']) {
        $in_thing .= $seperator . $in . $i . ',UN,0,' . $in . $i . ',IF';
        $out_thing .= $seperator . $out . $i . ',UN,0,' . $out . $i . ',IF';
        $pluses .= $plus;
        $seperator = ',';
        $plus = ',+';

        $rrd_options .= ' VDEF:totin' . $i . '=inB' . $i . ',TOTAL';
        $rrd_options .= ' VDEF:totout' . $i . '=outB' . $i . ',TOTAL';
        $rrd_options .= ' VDEF:tot' . $i . '=octets' . $i . ',TOTAL';
    }

    if ($i) {
        $stack = ':STACK';
    }

    $rrd_options .= ' AREA:inB' . $i . '#' . $colour_in . $stacked['transparency'] . ":'" . $descr . "'$stack";
    $rrd_options .= ' GPRINT:inB' . $i . ':LAST:%6.' . $float_precision . "lf%s$units";
    $rrd_options .= ' GPRINT:inB' . $i . ':AVERAGE:%6.' . $float_precision . "lf%s$units";
    $rrd_options .= ' GPRINT:inB' . $i . ':MAX:%6.' . $float_precision . "lf%s$units\l";

    if (! $nototal) {
        $rrd_options .= ' GPRINT:totin' . $i . ':%6.' . $float_precision . "lf%s$total_units";
    }

    $rrd_options .= " 'HRULE:0#" . $colour_out . ':' . $descr_out . "'";
    $rrd_optionsb .= " 'AREA:outB" . $i . '_neg#' . $colour_out . $stacked['transparency'] . ":$stack'";
    $rrd_options .= ' GPRINT:outB' . $i . ':LAST:%6.' . $float_precision . "lf%s$units";
    $rrd_options .= ' GPRINT:outB' . $i . ':AVERAGE:%6.' . $float_precision . "lf%s$units";
    $rrd_options .= ' GPRINT:outB' . $i . ':MAX:%6.' . $float_precision . "lf%s$units\l";

    if (! $nototal) {
        $rrd_options .= ' GPRINT:totout' . $i . ':%6.' . $float_precision . "lf%s$total_units";
    }

    $rrd_options .= " 'COMMENT:\l'";
    $i++;
    $iter++;
}

if ($custom_graph) {
    $rrd_options .= $custom_graph;
}

if (! $nototal) {
    $rrd_options .= ' CDEF:' . $in . 'octets=' . $in_thing . $pluses;
    $rrd_options .= ' CDEF:' . $out . 'octets=' . $out_thing . $pluses;
    $rrd_options .= ' CDEF:octets=inoctets,outoctets,+';
    $rrd_options .= ' CDEF:doutoctets=outoctets,' . $stacked['stacked'] . ',*';
    $rrd_options .= ' CDEF:inbits=inoctets,8,*';
    $rrd_options .= ' CDEF:outbits=outoctets,8,*';
    $rrd_options .= ' CDEF:doutbits=doutoctets,8,*';
    $rrd_options .= ' VDEF:percentile_in=inbits,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT';
    $rrd_options .= ' VDEF:percentile_out=outbits,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT';
    $rrd_options .= ' CDEF:dpercentile_outn=doutbits,' . $stacked['stacked'] . ',* VDEF:dpercentile_outnp=dpercentile_outn,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT CDEF:dpercentile_outnpn=doutbits,doutbits,-,dpercentile_outnp,' . $stacked['stacked'] . ',*,+ VDEF:dpercentile_out=dpercentile_outnpn,FIRST';
    $rrd_options .= ' VDEF:totin=inoctets,TOTAL';
    $rrd_options .= ' VDEF:totout=outoctets,TOTAL';
    $rrd_options .= ' VDEF:tot=octets,TOTAL';
}

$rrd_options .= $rrd_optionsb;
$rrd_options .= ' HRULE:0#999999';

unset($stacked);
