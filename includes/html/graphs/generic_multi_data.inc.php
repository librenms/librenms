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

if ($format == 'octets' || $format == 'bytes') {
    $units = 'Bps';
    $format = 'octets';
} else {
    $units = 'bps';
    $format = 'bits';
}

$i = 0;

foreach ($rrd_filenames as $key => $rrd_filename) {
    if ($rrd_inverted[$key]) {
        $in = 'out';
        $out = 'in';
    } else {
        $in = 'in';
        $out = 'out';
    }

    $rrd_options .= ' DEF:' . $in . 'octets' . $i . '=' . $rrd_filename . ':' . $ds_in . ':AVERAGE';
    $rrd_options .= ' DEF:' . $out . 'octets' . $i . '=' . $rrd_filename . ':' . $ds_out . ':AVERAGE';
    $in_thing .= $seperator . 'inoctets' . $i . ',UN,0,' . 'inoctets' . $i . ',IF';
    $out_thing .= $seperator . 'outoctets' . $i . ',UN,0,' . 'outoctets' . $i . ',IF';
    $pluses .= $plus;
    $seperator = ',';
    $plus = ',+';

    if ($_GET['previous']) {
        $rrd_options .= ' DEF:' . $in . 'octets' . $i . 'X=' . $rrd_filename . ':' . $ds_in . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' DEF:' . $out . 'octets' . $i . 'X=' . $rrd_filename . ':' . $ds_out . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:' . $in . 'octets' . $i . "X:$period";
        $rrd_options .= ' SHIFT:' . $out . 'octets' . $i . "X:$period";
        $in_thingX .= $seperatorX . 'inoctets' . $i . 'X,UN,0,' . 'inoctets' . $i . 'X,IF';
        $out_thingX .= $seperatorX . 'outoctets' . $i . 'X,UN,0,' . 'outoctets' . $i . 'X,IF';
        $plusesX .= $plusX;
        $seperatorX = ',';
        $plusX = ',+';
    }

    $i++;
}

if ($i) {
    if ($inverse) {
        $in = 'out';
        $out = 'in';
    } else {
        $in = 'in';
        $out = 'out';
    }

    $rrd_options .= ' CDEF:' . $in . 'octets=' . $in_thing . $pluses;
    $rrd_options .= ' CDEF:' . $out . 'octets=' . $out_thing . $pluses;
    $rrd_options .= ' CDEF:octets=inoctets,outoctets,+';
    $rrd_options .= ' CDEF:doutoctets=outoctets,' . $stacked['stacked'] . ',*';
    $rrd_options .= ' CDEF:inbits=inoctets,8,*';
    $rrd_options .= ' CDEF:outbits=outoctets,8,*';
    $rrd_options .= ' CDEF:doutbits=doutoctets,8,*';
    $rrd_options .= ' VDEF:percentile_in=inbits,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT';
    $rrd_options .= ' VDEF:percentile_out=outbits,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT';
    $rrd_options .= ' CDEF:dpercentile_outn=doutbits,' . $stacked['stacked'] . ',*';
    $rrd_options .= ' VDEF:dpercentile_outp=dpercentile_outn,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT';
    $rrd_options .= ' CDEF:dpercentile_outpn=doutbits,doutbits,-,dpercentile_outp,' . $stacked['stacked'] . ',*,+';
    $rrd_options .= ' VDEF:dpercentile_out=dpercentile_outpn,FIRST';
    $rrd_options .= ' VDEF:totin=inoctets,TOTAL';
    $rrd_options .= ' VDEF:totout=outoctets,TOTAL';
    $rrd_options .= ' VDEF:tot=octets,TOTAL';

    if ($_GET['previous'] == 'yes') {
        $rrd_options .= ' CDEF:' . $in . 'octetsX=' . $in_thingX . $pluses;
        $rrd_options .= ' CDEF:' . $out . 'octetsX=' . $out_thingX . $pluses;
        $rrd_options .= ' CDEF:doutoctetsX=outoctetsX,' . $stacked['stacked'] . ',*';
        $rrd_options .= ' CDEF:inbitsX=inoctetsX,8,*';
        $rrd_options .= ' CDEF:outbitsX=outoctetsX,8,*';
        $rrd_options .= ' CDEF:doutbitsX=doutoctetsX,8,*';
        $rrd_options .= ' VDEF:percentile_inX=inbitsX,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT';
        $rrd_options .= ' VDEF:percentile_outX=outbitsX,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT';
        $rrd_options .= ' CDEF:dpercentile_outXn=doutbitsX,' . $stacked['stacked'] . ',*';
        $rrd_options .= ' VDEF:dpercentile_outX=dpercentile_outXn,' . \LibreNMS\Config::get('percentile_value') . ',PERCENT';
        $rrd_options .= ' CDEF:dpercentile_outXn=doutbitsX,doutbitsX,-,dpercentile_outX,' . $stacked['stacked'] . ',*,+';
        $rrd_options .= ' VDEF:dpercentile_outX=dpercentile_outXn,FIRST';
    }

    if ($legend == 'no' || $legend == '1') {
        $rrd_options .= ' AREA:in' . $format . '#' . $colour_area_in . $stacked['transparency'] . ':';
        $rrd_options .= ' AREA:dout' . $format . '#' . $colour_area_out . $stacked['transparency'] . ':';
    } else {
        $rrd_options .= " COMMENT:'bps      Now       Ave      Max      " . \LibreNMS\Config::get('percentile_value') . "th %\\n'";
        $rrd_options .= ' AREA:in' . $format . '#' . $colour_area_in . $stacked['transparency'] . ':In ';
        $rrd_options .= ' GPRINT:in' . $format . ':LAST:%6.' . $float_precision . 'lf%s';
        $rrd_options .= ' GPRINT:in' . $format . ':AVERAGE:%6.' . $float_precision . 'lf%s';
        $rrd_options .= ' GPRINT:in' . $format . ':MAX:%6.' . $float_precision . 'lf%s';
        $rrd_options .= ' GPRINT:percentile_in:%6.' . $float_precision . 'lf%s\\n';
        $rrd_options .= ' AREA:dout' . $format . '#' . $colour_area_out . $stacked['transparency'] . ':Out';
        $rrd_options .= ' GPRINT:out' . $format . ':LAST:%6.' . $float_precision . 'lf%s';
        $rrd_options .= ' GPRINT:out' . $format . ':AVERAGE:%6.' . $float_precision . 'lf%s';
        $rrd_options .= ' GPRINT:out' . $format . ':MAX:%6.' . $float_precision . 'lf%s';
        $rrd_options .= ' GPRINT:percentile_out:%6.' . $float_precision . 'lf%s\\n';
        $rrd_options .= " GPRINT:tot:'Total %6." . $float_precision . "lf%sB'";
        $rrd_options .= " GPRINT:totin:'(In %6." . $float_precision . "lf%sB'";
        $rrd_options .= " GPRINT:totout:'Out %6." . $float_precision . "lf%sB)\\l'";
    }

    $rrd_options .= ' LINE1:percentile_in#aa0000';
    $rrd_options .= ' LINE1:dpercentile_out#aa0000';

    if ($_GET['previous'] == 'yes') {
        $rrd_options .= ' AREA:in' . $format . 'X#99999999' . $stacked['transparency'] . ':';
        $rrd_options .= ' AREA:dout' . $format . 'X#99999999' . $stacked['transparency'] . ':';
        $rrd_options .= ' LINE1:in' . $format . 'X#666666:';
        $rrd_options .= ' LINE1:dout' . $format . 'X#666666:';
    }
}

unset($stacked);
