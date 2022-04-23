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

if ($width > '500') {
    $descr_len = 24;
} else {
    $descr_len = 12;
}

if ($nototal) {
    $descr_len += '2';
    $unitlen += '2';
}

if ($width > '500') {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)) . "Now      Min      Max     Avg\l'";
    if (! $nototal) {
        $rrd_options .= " COMMENT:'Total      '";
    }

    $rrd_options .= " COMMENT:'\l'";
} else {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 5)), 0, ($descr_len + 5)) . "Now      Min      Max     Avg\l'";
}

$i = 0;
$iter = 0;
$ids = [];

foreach ($rrd_list as $rrd) {
    if (isset($rrd['colour'])) {
        $colour = $rrd['colour'];
    } else {
        if (! \LibreNMS\Config::get("graph_colours.$colours.$iter")) {
            $iter = 0;
        }
        $colour = \LibreNMS\Config::get("graph_colours.$colours.$iter");
        $iter++;
    }

    $ds = $rrd['ds'];
    $filename = $rrd['filename'];

    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr'], $descr_len);

    $ids[] = ($id = 'ds' . $i);

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
        $rrd_optionsc .= ' AREA:' . $id . 'i#' . $colour . $stacked['transparency'] . ":'$descr'" . $cstack;
        $rrd_optionsc .= ' GPRINT:' . $id . ':LAST:%5.1lf%s GPRINT:' . $id . 'min:MIN:%5.1lf%s';
        $rrd_optionsc .= ' GPRINT:' . $id . 'max:MAX:%5.1lf%s GPRINT:' . $id . ":AVERAGE:'%5.1lf%s\\n'";
        $cstack = ':STACK';
    } else {
        $rrd_optionsb .= ' AREA:' . $id . '#' . $colour . $stacked['transparency'] . ":'$descr'" . $bstack;
        $rrd_optionsb .= ' GPRINT:' . $id . ':LAST:%5.1lf%s GPRINT:' . $id . 'min:MIN:%5.1lf%s';
        $rrd_optionsb .= ' GPRINT:' . $id . 'max:MAX:%5.1lf%s GPRINT:' . $id . ":AVERAGE:'%5.1lf%s\\n'";
        $bstack = ':STACK';
    }

    $i++;
}

if ($print_total) {
    $tot = $ids;
    for ($i = 1; $i < count($ids); $i++) {
        $tot[] = '+';
    }

    $rrd_options .= ' CDEF:tot=' . implode($tot, ',');
    $rrd_options .= ' COMMENT:"  ' . \LibreNMS\Data\Store\Rrd::fixedSafeDescr('Total', $descr_len) . '"';
    $rrd_options .= ' GPRINT:tot:LAST:%5.1lf%s';
    $rrd_options .= ' GPRINT:tot:MIN:%5.1lf%s';
    $rrd_options .= ' GPRINT:tot:MAX:%5.1lf%s';
    $rrd_options .= ' GPRINT:tot:AVERAGE:%5.1lf%s\n';
}

$rrd_options .= $rrd_optionsb;
$rrd_options .= ' HRULE:0#555555';
$rrd_options .= $rrd_optionsc;

unset($stacked);
