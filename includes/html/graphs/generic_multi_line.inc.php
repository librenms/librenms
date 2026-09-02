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

$descr_len ??= 12;
$unitlen ??= 0;
$units ??= '';
$unit_text ??= '';
$multiplier ??= null;
$divider ??= null;
$rrd_optionsb = [];

if ($nototal) {
    $descr_len += '2';
    $unitlen += '2';
}

$rrd_options[] = 'COMMENT:' . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($unit_text, $descr_len) . "      Now      Min      Max     Avg\l";

$i = 0;
$iter = 0;

foreach ($rrd_list ?? [] as $rrd) {
    // get the color for this data set
    if (isset($rrd['colour'])) {
        $colour = $rrd['colour'];
    } else {
        if (! \App\Facades\LibrenmsConfig::get("graph_colours.$colours.$iter")) {
            $iter = 0;
        }
        $colour = \App\Facades\LibrenmsConfig::get("graph_colours.$colours.$iter");
        $iter++;
    }

    if (! empty($rrd['area']) && empty($rrd['areacolour'])) {
        $rrd['areacolour'] = $colour . '20';
    }

    $ds = $rrd['ds'];
    $filename = $rrd['filename'];

    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr'], $descr_len);

    $id = 'ds' . $i;

    $rrd_options[] = 'DEF:' . $id . "=$filename:$ds:AVERAGE";

    if (! empty($simple_rrd)) {
        $rrd_options[] = 'CDEF:' . $id . 'min=' . $id;
        $rrd_options[] = 'CDEF:' . $id . 'max=' . $id;
    } else {
        $rrd_options[] = 'DEF:' . $id . "min=$filename:$ds:MIN";
        $rrd_options[] = 'DEF:' . $id . "max=$filename:$ds:MAX";
    }

    // if we've been passed a multiplier or a divider (divisor!) we must scale the values for display
    $g_defname = $id;
    if (is_numeric($multiplier)) {
        $g_defname = $id . '_cdef';
        $rrd_options[] = 'CDEF:' . $g_defname . '=' . $id . ',' . $multiplier . ',*';
        $rrd_options[] = 'CDEF:' . $g_defname . 'min=' . $id . 'min,' . $multiplier . ',*';
        $rrd_options[] = 'CDEF:' . $g_defname . 'max=' . $id . 'max,' . $multiplier . ',*';
    } elseif (is_numeric($divider)) {
        $g_defname = $id . '_cdef';
        $rrd_options[] = 'CDEF:' . $g_defname . '=' . $id . ',' . $divider . ',/';
        $rrd_options[] = 'CDEF:' . $g_defname . 'min=' . $id . 'min,' . $divider . ',/';
        $rrd_options[] = 'CDEF:' . $g_defname . 'max=' . $id . 'max,' . $divider . ',/';
    }

    if (! empty($rrd['invert'])) {
        $rrd_options[] = 'CDEF:' . $g_defname . 'i=' . $g_defname . ',' . $stacked['stacked'] . ',*';

        $rrd_optionsb[] = 'LINE1.25:' . $g_defname . 'i#' . $colour . ":$descr";
        if (! empty($rrd['areacolour'])) {
            $rrd_optionsb[] = 'AREA:' . $g_defname . 'i#' . $rrd['areacolour'];
        }
    } else {
        $rrd_optionsb[] = 'LINE1.25:' . $g_defname . '#' . $colour . ":$descr";
        if (! empty($rrd['areacolour'])) {
            $rrd_optionsb[] = 'AREA:' . $g_defname . '#' . $rrd['areacolour'];
        }
    }

    $rrd_optionsb[] = 'GPRINT:' . $g_defname . ':LAST:%5.' . $float_precision . 'lf%s' . $units;
    $rrd_optionsb[] = 'GPRINT:' . $g_defname . 'min:MIN:%5.' . $float_precision . 'lf%s' . $units;
    $rrd_optionsb[] = 'GPRINT:' . $g_defname . 'max:MAX:%5.' . $float_precision . 'lf%s' . $units;
    $rrd_optionsb[] = 'GPRINT:' . $g_defname . ':AVERAGE:%5.' . $float_precision . "lf%s$units\\n";

    $i++;
}

array_push($rrd_options, ...$rrd_optionsb);
$rrd_options[] = 'HRULE:0#555555';

unset($stacked);
