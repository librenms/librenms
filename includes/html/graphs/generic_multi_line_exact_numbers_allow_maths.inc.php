<?php

require 'includes/html/graphs/common.inc.php';

$print_format = ! isset($print_format) ? '%8.0lf%s' : $print_format;

if (isset($lower_limit)) {
    $rrd_options .= ' --lower-limit ' . $lower_limit . ' ';
}

if (isset($upper_limit)) {
    $rrd_options .= ' --upper-limit ' . $upper_limit . ' ';
}

if ($width > '500') {
    $descr_len = $bigdescrlen;
} else {
    $descr_len = $smalldescrlen;
}

if ($printtotal === 1) {
    $descr_len += '2';
    $unitlen += '2';
}

$unit_text = str_pad(truncate($unit_text, $unitlen), $unitlen);

if ($width > '500') {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 10)), 0, ($descr_len + 10)) . "Now         Min         Max        Avg\l'";
    if ($printtotal === 1) {
        $rrd_options .= " COMMENT:'Total      '";
    }
    $rrd_options .= " COMMENT:'\l'";
} else {
    $rrd_options .= " COMMENT:'" . substr(str_pad($unit_text, ($descr_len + 10)), 0, ($descr_len + 10)) . "Now         Min         Max        Avg\l'";
}

foreach ($rrd_list as $rrd) {
    if ($rrd['colour']) {
        $colour = $rrd['colour'];
    } else {
        if (! \LibreNMS\Config::get("graph_colours.$colours.$colour_iter")) {
            $colour_iter = 0;
        }

        $colour = \LibreNMS\Config::get("graph_colours.$colours.$colour_iter");
        $colour_iter++;
    }
    $i++;

    $descr = \LibreNMS\Data\Store\Rrd::fixedSafeDescr($rrd['descr'], $descr_len);

    if (isset($rrd['cdef_rpn'])) {
        $rrd_options .= ' CDEF:' . $rrd['ds'] . '=' . $rrd['cdef_rpn']['val1'] . ',' . $rrd['cdef_rpn']['val2'] . ',' . $rrd['cdef_rpn']['oper'] . ' ';
    } else {
        $rrd_options .= ' DEF:' . $rrd['ds'] . '=' . $rrd['filename'] . ':' . $rrd['ds'] . ':AVERAGE ';
    }

    if ($simple_rrd) {
        $rrd_options .= ' CDEF:' . $rrd['ds'] . 'min=' . $rrd['ds'] . ' ';
        $rrd_options .= ' CDEF:' . $rrd['ds'] . 'max=' . $rrd['ds'] . ' ';
    } else {
        if (isset($rrd['cdef_rpn'])) {
            $rrd_options .= ' CDEF:' . $rrd['ds'] . 'min=' . $rrd['cdef_rpn']['val1'] . 'min,' . $rrd['cdef_rpn']['val2'] . 'min,' . $rrd['cdef_rpn']['oper'] . ' ';
            $rrd_options .= ' CDEF:' . $rrd['ds'] . 'max=' . $rrd['cdef_rpn']['val1'] . 'max,' . $rrd['cdef_rpn']['val2'] . 'max,' . $rrd['cdef_rpn']['oper'] . ' ';
        } else {
            $rrd_options .= ' DEF:' . $rrd['ds'] . 'min=' . $rrd['filename'] . ':' . $rrd['ds'] . ':MIN ';
            $rrd_options .= ' DEF:' . $rrd['ds'] . 'max=' . $rrd['filename'] . ':' . $rrd['ds'] . ':MAX ';
        }
    }

    if ($printtotal === 1) {
        $rrd_options .= ' VDEF:tot' . $rrd['ds'] . '=' . $rrd['ds'] . ',TOTAL';
    }

    $g_defname = $rrd['ds'];

    $f_multiplier = null;
    if (isset($rrd['multiplier']) && is_numeric($rrd['multiplier'])) {
        $f_multiplier = $rrd['multiplier'];
    } elseif (is_numeric($multiplier)) {
        $f_multiplier = $multiplier;
    }

    $f_divider = null;
    if (isset($rrd['divider']) && is_numeric($rrd['divider'])) {
        $f_divider = $rrd['divider'];
    } elseif (is_numeric($divider)) {
        $f_divider = $divider;
    }

    if (! is_null($f_multiplier) || ! is_null($f_divider)) {
        $g_defname = $rrd['ds'] . '_cdef';

        if ($f_multiplier) {
            $rrd_options .= ' CDEF:' . $g_defname . $i . '=' . $rrd['ds'] . $i . ',' . $f_multiplier . ',*';
            $rrd_options .= ' CDEF:' . $g_defname . $i . 'min=' . $rrd['ds'] . $i . ',' . $f_multiplier . ',*';
            $rrd_options .= ' CDEF:' . $g_defname . $i . 'max=' . $rrd['ds'] . $i . ',' . $f_multiplier . ',*';
        } elseif ($f_divider) {
            $rrd_options .= ' CDEF:' . $g_defname . $i . '=' . $rrd['ds'] . $i . ',' . $f_divider . ',/';
            $rrd_options .= ' CDEF:' . $g_defname . $i . 'min=' . $rrd['ds'] . $i . ',' . $f_divider . ',/';
            $rrd_options .= ' CDEF:' . $g_defname . $i . 'max=' . $rrd['ds'] . $i . ',' . $f_divider . ',/';
        }
    }

    if (isset($text_orig) && $text_orig) {
        $t_defname = $rrd['ds'];
    } else {
        $t_defname = $g_defname;
    }

    if ($i && ($dostack === 1)) {
        $stack = ':STACK';
    }

    $rrd_options .= ' LINE2:' . $g_defname . '#' . $colour . ":'" . $descr . "'$stack";
    $rrd_options .= ' GPRINT:' . $t_defname . ':LAST:' . $print_format . ' GPRINT:' . $t_defname . 'min:MIN:' . $print_format;
    $rrd_options .= ' GPRINT:' . $t_defname . 'max:MAX:' . $print_format . ' GPRINT:' . $t_defname . ':AVERAGE:' . $print_format . "'\\n'";

    if ($printtotal === 1) {
        $rrd_options .= ' GPRINT:tot' . $rrd['ds'] . ":%6.2lf%s'" . Rrd::safeDescr($total_units) . "'";
    }

    $rrd_options .= " COMMENT:'\\n'";
}//end foreach
