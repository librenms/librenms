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

if (! isset($descr_len)) {
    $descr_len = 12;
}

if ($nototal) {
    $descr_len += '2';
    $unitlen += '2';
}

$rrd_options .= " COMMENT:'" . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($unit_text, $descr_len) . "      Now      Min      Max     Avg\l'";

$i = 0;
$iter = 0;

foreach ($rrd_list as $rrd) {
    // get the color for this data set
    if (isset($rrd['colour'])) {
        $colour = $rrd['colour'];
    } else {
        if (! \LibreNMS\Config::get("graph_colours.$colours.$iter")) {
            $iter = 0;
        }
        $colour = \LibreNMS\Config::get("graph_colours.$colours.$iter");
        $iter++;
    }

    if (! empty($rrd['area']) && empty($rrd['areacolour'])) {
        $rrd['areacolour'] = $colour . '20';
    }

    $ds = $rrd['ds'];
    $filename = $rrd['filename'];

    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr'], $descr_len);

    $id = 'ds' . $i;

    $rrd_options .= ' DEF:' . $id . "=$filename:$ds:AVERAGE";

    if ($simple_rrd) {
        $rrd_options .= ' CDEF:' . $id . 'min=' . $id . ' ';
        $rrd_options .= ' CDEF:' . $id . 'max=' . $id . ' ';
    } else {
        $rrd_options .= ' DEF:' . $id . "min=$filename:$ds:MIN";
        $rrd_options .= ' DEF:' . $id . "max=$filename:$ds:MAX";
    }

    if ($rrd['invert']) {
        $rrd_options .= ' CDEF:' . $id . 'i=' . $id . ',' . $stacked['stacked'] . ',*';

        $rrd_optionsb .= ' LINE1.25:' . $id . 'i#' . $colour . ":'$descr'";
        if (! empty($rrd['areacolour'])) {
            $rrd_optionsb .= ' AREA:' . $id . 'i#' . $rrd['areacolour'];
        }
    } else {
        $rrd_optionsb .= ' LINE1.25:' . $id . '#' . $colour . ":'$descr'";
        if (! empty($rrd['areacolour'])) {
            $rrd_optionsb .= ' AREA:' . $id . '#' . $rrd['areacolour'];
        }
    }

    $rrd_optionsb .= ' GPRINT:' . $id . ':LAST:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . 'min:MIN:%5.' . $float_precision . 'lf%s' . $units;
    $rrd_optionsb .= ' GPRINT:' . $id . 'max:MAX:%5.' . $float_precision . 'lf%s' . $units . ' GPRINT:' . $id . ":AVERAGE:'%5." . $float_precision . "lf%s$units\\n'";

    $i++;
}

$rrd_options .= $rrd_optionsb;
$rrd_options .= ' HRULE:0#555555';

unset($stacked);
