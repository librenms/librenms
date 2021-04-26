<?php

require 'includes/html/graphs/common.inc.php';

if (! isset($descr_len)) {
    $descr_len = 12;
}

if ($nototal) {
    $descr_len += '2';
    $unitlen += '2';
}

$rrd_options .= " COMMENT:'" . \LibreNMS\Data\Store\Rrd::fixedSafeDescr($unit_text, $descr_len) . "        Now       Min       Max     Avg\l'";

$unitlen = '10';
if ($nototal) {
    $descr_len += '2';
    $unitlen += '2';
}

$unit_text = str_pad(truncate($unit_text, $unitlen), $unitlen);

$colour_iter = 0;
foreach ($rrd_list as $i => $rrd) {
    if ($rrd['colour']) {
        $colour = $rrd['colour'];
    } else {
        if (! \LibreNMS\Config::get("graph_colours.$colours.$colour_iter")) {
            $colour_iter = 0;
        }

        $colour = \LibreNMS\Config::get("graph_colours.$colours.$colour_iter");
        $colour_iter++;
    }

    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr'], $descr_len);

    $rrd_options .= ' DEF:' . $rrd['ds'] . $i . '=' . $rrd['filename'] . ':' . $rrd['ds'] . ':AVERAGE ';

    if ($simple_rrd) {
        $rrd_options .= ' CDEF:' . $rrd['ds'] . $i . 'min=' . $rrd['ds'] . $i . ' ';
        $rrd_options .= ' CDEF:' . $rrd['ds'] . $i . 'max=' . $rrd['ds'] . $i . ' ';
    } else {
        $rrd_options .= ' DEF:' . $rrd['ds'] . $i . 'min=' . $rrd['filename'] . ':' . $rrd['ds'] . ':MIN ';
        $rrd_options .= ' DEF:' . $rrd['ds'] . $i . 'max=' . $rrd['filename'] . ':' . $rrd['ds'] . ':MAX ';
    }

    if ($_GET['previous']) {
        $rrd_options .= ' DEF:' . $i . 'X=' . $rrd['filename'] . ':' . $rrd['ds'] . ':AVERAGE:start=' . $prev_from . ':end=' . $from;
        $rrd_options .= ' SHIFT:' . $i . "X:$period";
        $thingX .= $seperatorX . $i . 'X,UN,0,' . $i . 'X,IF';
        $plusesX .= $plusX;
        $seperatorX = ',';
        $plusX = ',+';
    }

    // Suppress totalling?
    if (! $nototal) {
        $rrd_options .= ' VDEF:tot' . $rrd['ds'] . $i . '=' . $rrd['ds'] . $i . ',TOTAL';
    }

    // This this not the first entry?
    if ($i) {
        $stack = ':STACK';
    }

    // if we've been passed a multiplier we must make a CDEF based on it!
    $g_defname = $rrd['ds'];
    if (is_numeric($multiplier)) {
        $g_defname = $rrd['ds'] . '_cdef';
        $rrd_options .= ' CDEF:' . $g_defname . $i . '=' . $rrd['ds'] . $i . ',' . $multiplier . ',*';
        $rrd_options .= ' CDEF:' . $g_defname . $i . 'min=' . $rrd['ds'] . $i . 'min,' . $multiplier . ',*';
        $rrd_options .= ' CDEF:' . $g_defname . $i . 'max=' . $rrd['ds'] . $i . 'max,' . $multiplier . ',*';

    // If we've been passed a divider (divisor!) we make a CDEF for it.
    } elseif (is_numeric($divider)) {
        $g_defname = $rrd['ds'] . '_cdef';
        $rrd_options .= ' CDEF:' . $g_defname . $i . '=' . $rrd['ds'] . $i . ',' . $divider . ',/';
        $rrd_options .= ' CDEF:' . $g_defname . $i . 'min=' . $rrd['ds'] . $i . 'min,' . $divider . ',/';
        $rrd_options .= ' CDEF:' . $g_defname . $i . 'max=' . $rrd['ds'] . $i . 'max,' . $divider . ',/';
    }

    // Are our text values related to te multiplier/divisor or not?
    if (isset($text_orig) && $text_orig) {
        $t_defname = $rrd['ds'];
    } else {
        $t_defname = $g_defname;
    }

    $rrd_options .= ' AREA:' . $g_defname . $i . '#' . $colour . ":'" . $descr . "'$stack";

    $rrd_options .= ' GPRINT:' . $t_defname . $i . ':LAST:%5.' . $float_precision . 'lf%s GPRINT:' . $t_defname . $i . 'min:MIN:%5.' . $float_precision . 'lf%s';
    $rrd_options .= ' GPRINT:' . $t_defname . $i . 'max:MAX:%5.' . $float_precision . 'lf%s GPRINT:' . $t_defname . $i . ":AVERAGE:'%5." . $float_precision . "lf%s\\n'";

    if (! $nototal) {
        $rrd_options .= ' GPRINT:tot' . $rrd['ds'] . $i . ':%6.' . $float_precision . "lf%s'" . \Rrd::safeDescr($total_units) . "'";
    }

    $rrd_options .= " COMMENT:'\\n'";
}//end foreach

if ($_GET['previous'] == 'yes') {
    if (is_numeric($multiplier)) {
        $rrd_options .= ' CDEF:X=' . $thingX . $plusesX . ',' . $multiplier . ',*';
    } elseif (is_numeric($divider)) {
        $rrd_options .= ' CDEF:X=' . $thingX . $plusesX . ',' . $divider . ',/';
    } else {
        $rrd_options .= ' CDEF:X=' . $thingX . $plusesX;
    }

    $rrd_options .= ' AREA:X#99999999:';
    $rrd_options .= ' LINE1.25:X#666666:';
}
